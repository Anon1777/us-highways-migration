<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>US Highway System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/static/style.css">
    <link rel="stylesheet" href="/static/style.css">
</head>
<body>
    <div class="nav">
        <nav class="navbar fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="/pages/index-redirect.html"><img class="branded-img" src="/static/img/US_blank.svg.png" id="brand-image"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="offcanvasDarkNavbarLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="offcanvasDarkNavbarLabel">Page Directory</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                            <li class="nav-item">
                                <a class="nav-link" href="/pages/interstates/list.php">Interstates</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/pages/us-highways/list.php">US Highways</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                    State Highways 
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/pages/state-highways/al/list.php">Alabama</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/ak/list.php">Alaska</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/az/list.php">Arizona</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/ar/list.php">Arkansas</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/ca/list.php">California</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/co/list.php">Colorado</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/ct/list.php">Connecticut</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/de/list.php">Delaware</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/dc/list.php">District of Columbia</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/fl/list.php">Florida</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/ga/list.php">Georgia</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/hi/list.php">Hawaii</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/id/list.php">Idaho</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/il/list.php">Illinois</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/in/list.php">Indiana</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/ia/list.php">Iowa</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/ks/list.php">Kansas</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/ky/list.php">Kentucky</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/la/list.php">Louisiana</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/me/list.php">Maine</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/md/list.php">Maryland</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/ma/list.php">Massachussetts</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/mi/list.php">Michigan</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/mn/list.php">Minnesota</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/ms/list.php">Mississippi</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/mo/list.php">Missouri</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/mt/list.php">Montana</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/ne/list.php">Nebraska</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/nh/list.php">New Hampshire</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/nj/list.php">New Jersey</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/nm/list.php">New Mexico</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/ny/list.php">New York</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/nv/list.php">Nevada</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/nc/list.php">North Carolina</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/nd/list.php">North Dakota</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/oh/list.php">Ohio</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/ok/list.php">Oklahoma</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/or/list.php">Oregon</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/pa/list.php">Pennsylvania</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/ri/list.php">Rhode Island</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/sc/list.php">South Carolina</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/sd/list.php">South Dakota</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/tn/list.php">Tennessee</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/tx/list.php">Texas</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/ut/list.php">Utah</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/vt/list.php">Vermont</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/va/list.php">Virginia</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/wa/list.php">Washington</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/wv/list.php">West Virginia</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/wi/list.php">Wisconsin</a></li>
                                    <li><a class="dropdown-item" href="/pages/state-highways/wy/list.php">Wyoming</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                    Secondary State Roads
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark">
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">California</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="" class="dropdown-item"></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Florida</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="" class="dropdown-item"></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Iowa</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="" class="dropdown-item"></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Michigan</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="" class="dropdown-item"></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Minnesota</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="" class="dropdown-item"></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">New Jersey</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="/pages/county-roads/nj/at/list.php" class="dropdown-item">Atlantic</a></li>
                                            <li><a href="/pages/county-roads/nj/be/list.php" class="dropdown-item">Bergen</a></li>
                                            <li><a href="/pages/county-roads/nj/bu/list.php" class="dropdown-item">Burlington</a></li>
                                            <li><a href="/pages/county-roads/nj/ca/list.php" class="dropdown-item">Camden</a></li>
                                            <li><a href="/pages/county-roads/nj/cm/list.php" class="dropdown-item">Cape May</a></li>
                                            <li><a href="/pages/county-roads/nj/cu/list.php" class="dropdown-item">Cumberland</a></li>
                                            <li><a href="/pages/county-roads/nj/es/list.php" class="dropdown-item">Essex</a></li>
                                            <li><a href="/pages/county-roads/nj/gl/list.php" class="dropdown-item">Gloucester</a></li>
                                            <li><a href="/pages/county-roads/nj/hu/list.php" class="dropdown-item">Hudson</a></li>
                                            <li><a href="/pages/county-roads/nj/hn/list.php" class="dropdown-item">Hunterdon</a></li>
                                            <li><a href="/pages/county-roads/nj/me/list.php" class="dropdown-item">Mercer</a></li>
                                            <li><a href="/pages/county-roads/nj/mid/list.php" class="dropdown-item">Middlesex</a></li>
                                            <li><a href="/pages/county-roads/nj/mo/list.php" class="dropdown-item">Monmouth</a></li>
                                            <li><a href="/pages/county-roads/nj/mr/list.php" class="dropdown-item">Morris</a></li>
                                            <li><a href="/pages/county-roads/nj/oc/list.php" class="dropdown-item">Ocean</a></li>
                                            <li><a href="/pages/county-roads/nj/pa/list.php" class="dropdown-item">Passaic</a></li>
                                            <li><a href="/pages/county-roads/nj/sa/list.php" class="dropdown-item">Salem</a></li>
                                            <li><a href="/pages/county-roads/nj/so/list.php" class="dropdown-item">Somerset</a></li>
                                            <li><a href="/pages/county-roads/nj/su/list.php" class="dropdown-item">Sussex</a></li>
                                            <li><a href="/pages/county-roads/nj/un/list.php" class="dropdown-item">Union</a></li>
                                            <li><a href="/pages/county-roads/nj/wa/list.php" class="dropdown-item">Warren</a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">New York</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="" class="dropdown-item"></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Ohio</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="" class="dropdown-item"></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Pennsylvania</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="" class="dropdown-item"></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Virginia</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="" class="dropdown-item"></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Wisconsin</a>
                                        <ul class="dropdown-menu">
                                            <li><a href="" class="dropdown-item"></a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                        <form class="d-flex mt-3" role="search">
                            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                            <button class="btn btn-success" type="submit">Search</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
    </div>
    <div class="row" id="list-redirects">
        <div class="col col-1 xxl"></div>
        <div class="col col-10 xxl">