from ortools.constraint_solver import pywrapcp, routing_enums_pb2
import json, os, time, math, folium, requests

cache_file = "sneaky_little_google_maps_clone/route_cache.json"

# lightweight timer for terminal output
_timer_start = time.time()
def _tick(label: str):
    now = time.time()
    elapsed = now - _timer_start
    print(f"[TIMER] {label}: {elapsed:.2f}s elapsed")
    return now

if os.path.exists(cache_file):
    with open(cache_file, "r") as f:
        route_cache = json.load(f)
else:
    route_cache = {}

def haversine(a, b):
    lat1, lon1 = map(math.radians, a)
    lat2, lon2 = map(math.radians, b)
    dlat = lat2 - lat1
    dlon = lon2 - lon1
    r = 6371000
    h = math.sin(dlat / 2) ** 2 + math.cos(lat1) * math.cos(lat2) * math.sin(dlon/2) ** 2
    return 2 * r * math.asin(math.sqrt(h))

def haversine_matrix(coords):
    n = len(coords)
    matrix = [[0]*n for _ in range(n)]

    for i in range(n):
        for j in range(n):
            if i == j:
                matrix[i][j] = 0
            else:
                d = haversine(coords[i], coords[j])
                matrix[i][j] = int(d)
    return matrix

MAX_METERS = 500_000

def preflighted_distance_matrix(coords, cache=route_cache):
    """
    coords = [(lat, lon), ...]
    returns NxN distance matrix in meters, preflighted with OSRM.
    If OSRM cannot route an edge, marks it as MAX_METERS.
    Preserves asymmetry.
    """
    n = len(coords)
    matrix = [[MAX_METERS for _ in range(n)] for _ in range(n)]

    for i in range(n):
        for j in range(n):
            if i == j:
                matrix[i][j] = 0
                continue

            a, b = coords[i], coords[j]
            key = f"{round(a[0],6)}-{round(a[1],6)}-{round(b[0],6)}-{round(b[1],6)}"

            # Check cache first
            if key in cache:
                try:
                    # distance along cached path
                    path = cache[key]
                    # sum haversine along segments if path has >2 points
                    # (2-point paths are just straight haversine lines, not real routes)
                    if len(path) <= 2:
                        dist = haversine(a, b)
                    else:
                        dist = sum(haversine(path[k], path[k+1]) for k in range(len(path)-1))
                    matrix[i][j] = int(dist)
                    continue
                except Exception:
                    # fallback to BIG
                    matrix[i][j] = MAX_METERS
                    continue

            # Try OSRM
            try:
                path = osrm_route_geometry_safe(a, b)
                # compute length along real roads
                dist = sum(haversine(path[k], path[k+1]) for k in range(len(path)-1))
                matrix[i][j] = int(dist)
                # store in cache
                cache[key] = path
            except Exception:
                # OSRM failed → mark as BIG
                matrix[i][j] = MAX_METERS

    return matrix

def osrm_distance_matrix(coords):
    """
    coords = [(lat, lon), ...]
    returns matrix in meters
    """
    # Build full NxN distance matrix by querying OSRM in smaller chunks.
    # OSRM's public table API can reject very long URLs; to avoid that we
    # split coordinates into chunks and request sub-matrices for each
    # pair of chunks. This assembles the full distance matrix.

    n = len(coords)
    if n == 0:
        return []

    # Choose a chunk size that keeps URLs short; tune if needed.
    CHUNK_SIZE = 25

    # initialize full matrix
    matrix = [[0.0 for _ in range(n)] for _ in range(n)]

    # helper to format a list of coords to OSRM coordinate strings
    def fmt(coord_list):
        return ";".join([f"{lon},{lat}" for lat, lon in coord_list])

    # create list of chunks (list of (start_index, coords_slice))
    chunks = []
    for start in range(0, n, CHUNK_SIZE):
        chunks.append((start, coords[start:start + CHUNK_SIZE]))

    base_url = "https://router.project-osrm.org/table/v1/driving/"

    for i_idx, (i_start, i_coords) in enumerate(chunks):
        for j_idx, (j_start, j_coords) in enumerate(chunks):
            # coordinates for this request: sources chunk followed by destinations chunk
            combined = i_coords + j_coords
            coord_str = fmt(combined)

            # sources are 0..len(i_coords)-1, destinations are len(i_coords)..len(combined)-1
            sources = ";".join(str(k) for k in range(0, len(i_coords)))
            dests = ";".join(str(k) for k in range(len(i_coords), len(combined)))

            url = f"{base_url}{coord_str}"
            params = {"annotations": "distance", "sources": sources, "destinations": dests}

            r = requests.get(url, params=params, timeout=30)
            try:
                r.raise_for_status()
            except requests.exceptions.HTTPError as e:
                # propagate a clear error including which chunks failed
                raise RuntimeError(f"OSRM table API error for chunks {i_start}:{i_start+len(i_coords)} -> {j_start}:{j_start+len(j_coords)}: {e}\nResponse:\n{r.text}")

            data = r.json()
            sub = data.get("distances")
            if sub is None:
                raise RuntimeError(f"OSRM returned no distances for chunks {i_start}->{j_start}: {data}")

            # write submatrix into full matrix
            for ii, row in enumerate(sub):
                for jj, val in enumerate(row):
                    if val is None or val > MAX_METERS:
                        matrix[i_start + ii][j_start + jj] = OSRM_CUTOFF_METERS
                    else:
                        matrix[i_start + ii][j_start + jj] = int(val)


    return matrix

OSRM_CUTOFF_METERS = math.pow(2, 31) - 1

def preflight_osrm_matrix(coords):
    n = len(coords)
    matrix = [[0]*n for _ in range(n)]
    for i in range(n):
        for j in range(n):
            if i == j:
                matrix[i][j] = 0
                continue
            try:
                path = osrm_route_geometry_safe(coords[i], coords[j])
                dist = sum(haversine(path[k], path[k+1]) for k in range(len(path)-1))
                matrix[i][j] = int(dist)
            except RuntimeError:
                matrix[i][j] = OSRM_CUTOFF_METERS
    return matrix

def osrm_route_geometry(a, b, cache=route_cache):
    """
    a, b = (lat, lon)
    returns list of (lat, lon) following real roads
    """
    key = f"{round(a[0], 6)}-{round(a[1], 6)}-{round(b[0], 6)}-{round(b[1], 6)}"

    if key in cache:
        return cache[key]

    lon1, lat1 = a[1], a[0]
    lon2, lat2 = b[1], b[0]

    url = (
        f"https://router.project-osrm.org/route/v1/driving/"
        f"{lon1},{lat1};{lon2},{lat2}"
        f"?overview=full&geometries=geojson"
    )
    print("Routing: ", a, "->", b)
    r = requests.get(url, timeout=10)
    if r.status_code != 200 or not r.json().get("routes"):
        # print("OSRM error:", r.status_code, r.text)
        # return [a, b]
        raise RuntimeError(f"OSRM failed on edge {a} -> {b}")

    coords = r.json()["routes"][0]["geometry"]["coordinates"]
    path = [(lat, lon) for lon, lat in coords]

    cache[key] = path

    rev_key = f"{b}-{a}"
    cache[rev_key] = list(reversed(path))

    return path

def osrm_route_geometry_safe(a, b, cache=route_cache):
    """
    a, b = (lat, lon)
    Returns list of (lat, lon) along real roads.
    Raises RuntimeError if OSRM cannot route.
    NEVER returns a straight line for failed edges.
    """
    key = f"{round(a[0], 6)}-{round(a[1], 6)}-{round(b[0], 6)}-{round(b[1], 6)}"
    if key in cache:
        return cache[key]

    lon1, lat1 = a[1], a[0]
    lon2, lat2 = b[1], b[0]

    url = (
        f"https://router.project-osrm.org/route/v1/driving/"
        f"{lon1},{lat1};{lon2},{lat2}"
        f"?overview=full&geometries=geojson"
    )
    r = requests.get(url, timeout=10)
    if r.status_code != 200:
        raise RuntimeError(f"OSRM failed for {a} -> {b} with status {r.status_code}")

    routes = r.json().get("routes")
    if not routes or len(routes) == 0:
        raise RuntimeError(f"OSRM returned no routes for {a} -> {b}")

    coords = routes[0]["geometry"]["coordinates"]
    if not coords or len(coords) < 2:
        raise RuntimeError(f"OSRM returned invalid geometry for {a} -> {b}")

    path = [(lat, lon) for lon, lat in coords]
    cache[key] = path

    # also store reversed path for efficiency
    rev_key = f"{round(b[0], 6)}-{round(b[1], 6)}-{round(a[0], 6)}-{round(a[1], 6)}"
    cache[rev_key] = list(reversed(path))

    return path

# load locations from external JSON to keep this file small
locations_file = "sneaky_little_google_maps_clone/locations.json"
with open(locations_file, "r", encoding="utf-8") as lf:
    locations_list = json.load(lf)

# build a dict preserving the JSON order: {(lat, lon): name}
locations = {(item["lat"], item["lon"]): item["name"] for item in locations_list}

# create coord and name lists for internal use (OR-Tools, folium)
coords_locations = list(locations.keys())
names_locations = list(locations.values())

# locations2 as accepted schools
locations2 = dict(list(locations.items())[:22])
coords_locations2 = list(locations2.keys())
names_locations2 = list(locations2.values())

# locations3 as declined schools
locations3 = dict(list(locations.items())[len(locations)-5:])
coords_locations3 = list(locations3.keys())
names_locations3 = list(locations3.values())

# distance_matrix = osrm_distance_matrix(coords_locations)
distance_matrix = haversine_matrix(coords_locations)
_tick("computed distance_matrix for all locations")

# --- first route (locations) ---
manager = pywrapcp.RoutingIndexManager(len(coords_locations), 1, 0)
routing = pywrapcp.RoutingModel(manager)

def distance_callback(from_i, to_i):
    return int(distance_matrix[
        manager.IndexToNode(from_i)
    ][
        manager.IndexToNode(to_i)
    ])

transit = routing.RegisterTransitCallback(distance_callback)
routing.SetArcCostEvaluatorOfAllVehicles(transit)

params = pywrapcp.DefaultRoutingSearchParameters()
params.first_solution_strategy = routing_enums_pb2.FirstSolutionStrategy.PATH_CHEAPEST_ARC

solution = routing.SolveWithParameters(params)

route = []
i = routing.Start(0)
while not routing.IsEnd(i):
    route.append(manager.IndexToNode(i))
    i = solution.Value(routing.NextVar(i))
route.append(manager.IndexToNode(i))  # add the end node

full_road_path = []
for i in range(len(route) - 1):
    a = coords_locations[route[i]]
    b = coords_locations[route[i + 1]]
    segment = osrm_route_geometry(a, b)
    full_road_path.extend(segment if i == 0 else segment[1:])

_tick("constructed full_road_path (first route)")


# --- second route (locations2) ---
# distance_matrix2 = osrm_distance_matrix(coords_locations2)
distance_matrix2 = haversine_matrix(coords_locations2)
_tick("computed distance_matrix2 for supported locations")
manager2 = pywrapcp.RoutingIndexManager(len(coords_locations2), 1, 0)
routing2 = pywrapcp.RoutingModel(manager2)

def distance_callback2(from_i, to_i):
    return int(distance_matrix2[
        manager2.IndexToNode(from_i)
    ][
        manager2.IndexToNode(to_i)
    ])

transit2 = routing2.RegisterTransitCallback(distance_callback2)
routing2.SetArcCostEvaluatorOfAllVehicles(transit2)

params2 = pywrapcp.DefaultRoutingSearchParameters()
params2.first_solution_strategy = routing_enums_pb2.FirstSolutionStrategy.PATH_CHEAPEST_ARC

solution2 = routing2.SolveWithParameters(params2)

route2 = []
i2 = routing2.Start(0)
while not routing2.IsEnd(i2):
    route2.append(manager2.IndexToNode(i2))
    i2 = solution2.Value(routing2.NextVar(i2))
route2.append(manager2.IndexToNode(i2))

full_road_path2 = []
for i in range(len(route2) - 1):
    a = coords_locations2[route2[i]]
    b = coords_locations2[route2[i + 1]]
    try:
        segment = osrm_route_geometry(a, b)
        # Only use the segment if it's a real route (more than 2 points)
        if len(segment) <= 2:
            print(f"WARNING: Edge {a} -> {b} is a haversine line, skipping")
            segment = []
    except RuntimeError:
        print(f"WARNING: OSRM routing failed for {a} -> {b}")
        segment = []
    full_road_path2.extend(segment if i == 0 else segment[1:] if segment else [])

_tick("constructed full_road_path2 (second route)")


# --- third route (locations3) ---
# distance_matrix3 = osrm_distance_matrix(coords_locations3)
distance_matrix3 = haversine_matrix(coords_locations3)
_tick("computed distance_matrix3 for declined locations")
manager3 = pywrapcp.RoutingIndexManager(len(coords_locations3), 1, 0)
routing3 = pywrapcp.RoutingModel(manager3)

def distance_callback3(from_i, to_i):
    return int(distance_matrix3[
        manager3.IndexToNode(from_i)
    ][
        manager3.IndexToNode(to_i)
    ])

transit3 = routing3.RegisterTransitCallback(distance_callback3)
routing3.SetArcCostEvaluatorOfAllVehicles(transit3)

params3 = pywrapcp.DefaultRoutingSearchParameters()
params3.first_solution_strategy = routing_enums_pb2.FirstSolutionStrategy.PATH_CHEAPEST_ARC

solution3 = routing3.SolveWithParameters(params3)

route3 = []
i3 = routing3.Start(0)
while not routing3.IsEnd(i3):
    route3.append(manager3.IndexToNode(i3))
    i3 = solution3.Value(routing3.NextVar(i3))
route3.append(manager3.IndexToNode(i3))

full_road_path3 = []
for i in range(len(route3) - 1):
    a = coords_locations3[route3[i]]
    b = coords_locations3[route3[i + 1]]
    try:
        segment = osrm_route_geometry(a, b)
        # Only use the segment if it's a real route (more than 2 points)
        if len(segment) <= 2:
            print(f"WARNING: Edge {a} -> {b} is a haversine line, skipping")
            segment = []
    except RuntimeError:
        print(f"WARNING: OSRM routing failed for {a} -> {b}")
        segment = []
    full_road_path3.extend(segment if i == 0 else segment[1:] if segment else [])

_tick("constructed full_road_path3 (third route)")


# create map
m = folium.Map(location=coords_locations[0], zoom_start=8)

# Feature groups: separate groups for markers (pins) and polylines (routes)
# so users can toggle pin visuals independently from the route lines.
mg1 = folium.FeatureGroup(name="All Schools Pins", show=True)
lg1 = folium.FeatureGroup(name="All Schools Route", show=True)

mg2 = folium.FeatureGroup(name="Supported Schools Pins", show=True)
lg2 = folium.FeatureGroup(name="Supported Schools Route", show=True)

mg3 = folium.FeatureGroup(name="Declined Schools Pins", show=True)
lg3 = folium.FeatureGroup(name="Declined Schools Route", show=True)

# add markers for first route
ordered_points = [coords_locations[i] for i in route]
ordered_names = [names_locations[i] for i in route]
for idx, point in enumerate(ordered_points):
    popup = folium.Popup(f"Stop #{idx+1} - {ordered_names[idx]}", max_width=320)
    folium.Marker(
        point,
        popup=popup,
        icon=folium.Icon(color="blue", icon="info-sign")
    ).add_to(mg1)

# add markers for second route
ordered_points2 = [coords_locations2[i] for i in route2]
ordered_names2 = [names_locations2[i] for i in route2]
for idx, point in enumerate(ordered_points2):
    popup = folium.Popup(f"Stop #{idx+1} - {ordered_names2[idx]}", max_width=320)
    folium.Marker(
        point,
        popup=popup,
        icon=folium.Icon(color="green", icon="info-sign")
    ).add_to(mg2)

# add markers for third route
ordered_points3 = [coords_locations3[i] for i in route3]
ordered_names3 = [names_locations3[i] for i in route3]
for idx, point in enumerate(ordered_points3):
    popup = folium.Popup(f"Stop #{idx+1} - {ordered_names3[idx]}", max_width=320)
    folium.Marker(
        point,
        popup=popup,
        icon=folium.Icon(color="red", icon="info-sign")
    ).add_to(mg3)

# add polylines to respective feature groups
# add polylines to respective line feature groups
folium.PolyLine(full_road_path, weight=5, color="blue", opacity=0.5).add_to(lg1)
folium.PolyLine(full_road_path2, weight=5, color="green", opacity=0.5).add_to(lg2)
folium.PolyLine(full_road_path3, weight=5, color="red", opacity=0.5).add_to(lg3)

# add feature groups and layer control
mg1.add_to(m)
lg1.add_to(m)

mg2.add_to(m)
lg2.add_to(m)

mg3.add_to(m)
lg3.add_to(m)

folium.LayerControl().add_to(m)

m.save("sneaky_little_google_maps_clone/route.html")

_tick("saved route.html")

total = time.time() - _timer_start
print(f"[TIMER] total runtime: {total:.2f}s")

with open(cache_file, "w") as f:
    json.dump(route_cache, f)