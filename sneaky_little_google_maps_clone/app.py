import requests
from ortools.constraint_solver import pywrapcp, routing_enums_pb2
import folium
import json, os

cache_file = "route_cache.json"

if os.path.exists(cache_file):
    with open(cache_file, "r") as f:
        route_cache = json.load(f)
else:
    route_cache = {}

def osrm_distance_matrix(coords):
    """
    coords = [(lat, lon), ...]
    returns matrix in meters
    """

    coord_str = ";".join([f"{lon},{lat}" for lat, lon in coords])
    url = f"https://router.project-osrm.org/table/v1/driving/{coord_str}?annotations=distance"

    r = requests.get(url)
    r.raise_for_status()
    data = r.json()

    return data["distances"]

def osrm_route_geometry(a, b, cache=route_cache):
    """
    a, b = (lat, lon)
    returns list of (lat, lon) following real roads
    """
    key = f"{a}-{b}"

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
    if r.status_code != 200:
        print("OSRM error:", r.status_code, r.text)
        return [a, b]

    coords = r.json()["routes"][0]["geometry"]["coordinates"]
    path = [(lat, lon) for lon, lat in coords]

    cache[key] = path
    return path

locations = { # colleges -> converted to pair->string dictionary
    (40.271193489833, -74.775986260087): "The College of New Jersey",
    (40.74263018065103, -74.17846842212802): "New Jersey Institute of Technology",
    (40.280294594726605, -74.74312405046807): "Rider University",
    (42.72936533006677, -73.68172144464765): "Rensselaer Polytechnic Institute",
    (40.74316519392738, -74.02658458457552): "Stevens Institute of Technology",
    (40.74531472799729, -74.24330301588702): "Seton Hall University",
    (40.52319572675335, -74.45756700240462): "Rutgers University",
    (43.08439257838247, -77.66792875997953): "Rochester Institute of Technology",
    (40.32503793035759, -74.13269483124854): "Brookdale Community College",
    (39.71112575087736, -75.11530688708902): "Rowan University",
    (39.78004292808525, -75.124079934979): "Rowan College of South Jersey",
    (39.954117444258586, -75.18686060242587): "Drexel University",
    (39.67776105856646, -75.75393826010897): "University of Delaware",
    (40.80054631448078, -77.86573480239434): "Pennsylvania State University",
    (43.10465129063488, -77.6134605970928): "Monroe Community College",
    (43.03993147101748, -71.45454100230837): "Southern New Hampshire University",
    (40.609710271287106, -74.68851706009562): "Raritan Valley Community College",
    (40.84715856227562, -74.83429347566346): "Centenary University",
    (40.5086561140884, -75.7831961735687): "Kutztown University of Pennsylvania",
    (41.808866671129515, -72.24990660235612): "University of Connecticut",
    (43.123976257373855, -77.63160086869492): "University of Rochester",
    (41.720010279205695, -73.93668173119595): "Marist College",
    (41.081609842657045, -74.17539502978748): "Ramapo College of New Jersey",
    (40.95210207123912, -74.090242231225): "Bergen Community College",
    (39.95340834451453, -75.19380977358941): "University of Pennsylvania",
    (40.85977479996927, -74.19975613122853): "Montclair State University",
    (38.986027650624656, -76.93994173129761): "University of Maryland",
    (42.42169244512942, -76.50090434841817): "Ithaca College",
    (40.598189773901, -75.50812924759325): "Muhlenberg College",
    (36.071952500465066, -79.77591920256314): "North Carolina A&T State University",
    (42.08724852151559, -75.96452854501588): "Binghamton University",
    (43.00104071034751, -78.78775863114623): "University at Buffalo",
    (40.44426003636217, -79.95333117357117): "University of Pittsburgh",
    (39.953311847017716, -75.5983684024258): "West Chester University",
    (42.67863757200578, -73.8236939467176): "University at Albany",
    (40.098450026931054, -74.22645577521526): "Georgian Court University",
    (39.49032177148517, -74.53153007360639): "Stockton University",
    (42.35095352898789, -71.10386260233525): "Boston University",
    (39.98032629252742, -75.15703593126126): "Temple University",
    (39.681834883587456, -75.58624876466958): "Wilmington University",
    (40.44320579546058, -79.94283560030024): "Carnegie Mellon University",
    (43.03859417101759, -76.136915273472): "Syracuse University",
    (40.67603023362234, -74.23438439808363): "Kean University",
    (40.03678071357278, -75.34415736131862): "Villanova University",
    (40.27945134140807, -74.00655270241377): "Monmouth University",
    (37.57582123211499, -77.53975625439209): "University of Richmond",
    (40.83395910629634, -74.27198614075759): "Caldwell University",
    (40.06440062513395, -79.88445442899733): "Pennsylvania Western University",
    (40.76022097122582, -74.4266543904623): "Drew University",
    (42.33613560190064, -71.16927073117216): "Boston College",
    (38.035654971803794, -78.50336883133161): "University of Virginia",
    (40.709656160951795, -74.08761630067153): "New Jersey City University",
    (35.78948035016143, -78.63739688511436): "William Peace University",
    (40.94762526157357, -74.19678699519119): "William Paterson University",
    (40.7773693712645, -74.43413553123162): "Fairleigh Dickinson University",
    (42.35924437107272, -71.09314963117141): "Massachusetts Institute of Technology",
    (40.25703464683813, -74.6500411735781): "Mercer County Community College",
    (40.85817367125266, -74.58147056006503): "County College of Morris",
    (40.348328849577825, -74.66062975851233): "Princeton University",
    (40.806801871260085, -73.96166283123058): "Columbia University",
    (42.37439172730471, -71.11626416826554): "Harvard University",
    (43.70168409646937, -72.29035786548539): "Dartmouth College",
    (42.4521620872413, -76.48033008884057): "Cornell University",
    (41.82684087112749, -71.40308830235546): "Brown University",
    (39.16608652846738, -86.52525684267435): "Indiana University",
}

# create coord and name lists for internal use (OR-Tools, folium)
coords_locations = list(locations.keys())
names_locations = list(locations.values())

# locations2 as first 13 entries from the dict
locations2 = dict(list(locations.items())[:13])
coords_locations2 = list(locations2.keys())
names_locations2 = list(locations2.values())

distance_matrix = osrm_distance_matrix(coords_locations)

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


# --- second route (locations2) ---
distance_matrix2 = osrm_distance_matrix(coords_locations2)
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
    segment = osrm_route_geometry(a, b)
    full_road_path2.extend(segment if i == 0 else segment[1:])


# create map
m = folium.Map(location=coords_locations[0], zoom_start=8)

# Feature groups so routes/markers can be toggled independently
fg1 = folium.FeatureGroup(name="Route 1", show=True)
fg2 = folium.FeatureGroup(name="Route 2", show=False)

# add markers for first route
ordered_points = [coords_locations[i] for i in route]
ordered_names = [names_locations[i] for i in route]
for idx, point in enumerate(ordered_points):
    folium.Marker(
        point,
        popup=ordered_names[idx],
        icon=folium.Icon(color="blue", icon="info-sign")
    ).add_to(fg1)

# add markers for second route
ordered_points2 = [coords_locations2[i] for i in route2]
ordered_names2 = [names_locations2[i] for i in route2]
for idx, point in enumerate(ordered_points2):
    folium.Marker(
        point,
        popup=ordered_names2[idx],
        icon=folium.Icon(color="green", icon="info-sign")
    ).add_to(fg2)

# add polylines to respective feature groups
folium.PolyLine(full_road_path, weight=5, color="blue", opacity=0.5).add_to(fg1)
folium.PolyLine(full_road_path2, weight=5, color="green", opacity=0.5).add_to(fg2)

# add feature groups and layer control
fg1.add_to(m)
fg2.add_to(m)
folium.LayerControl().add_to(m)

m.save("route.html")

with open(cache_file, "w") as f:
    json.dump(route_cache, f)