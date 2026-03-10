<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Inventario de Balatas')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: #f6f8fb;
        }
        .page-title {
            font-size: 1.3rem;
            margin-bottom: 0;
        }
        .search-form .form-control {
            min-width: 0;
        }
        .mobile-card {
            border: 1px solid #e7e8ec;
            border-radius: 10px;
            background: #fff;
        }
        .mobile-card .label {
            color: #6c757d;
            font-size: .82rem;
            margin-bottom: 2px;
        }
        .mobile-card .value {
            word-break: break-word;
        }
        .thumb {
            width: 74px;
            height: 74px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .gallery-slide {
            max-height: 75vh;
            object-fit: contain;
            width: 100%;
            background: #000;
        }
        @media (max-width: 767.98px) {
            .page-title {
                font-size: 1.1rem;
            }
            .thumb {
                width: 64px;
                height: 64px;
            }
            .btn-mobile-block {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('balatas.index') }}">Inventario Balatas</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            @auth
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('balatas.index') || request()->routeIs('balatas.create') || request()->routeIs('balatas.edit') ? 'active' : '' }}" href="{{ route('balatas.index') }}">Gestión de Balatas</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('balatas.catalogo') ? 'active' : '' }}" href="{{ route('balatas.catalogo') }}">Catalogo</a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('ventas.*') ? 'active' : '' }}" href="{{ route('ventas.index') }}">Ventas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tarimas.*') ? 'active' : '' }}" href="{{ route('tarimas.index') }}">Tarimas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('talleres.*') ? 'active' : '' }}" href="{{ route('talleres.index') }}">Directorio</a>
                    </li>
                </ul>
                <form method="POST" action="{{ route('logout') }}" class="d-flex">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Cerrar sesion</button>
                </form>
            @endauth
        </div>
    </div>
</nav>

<main class="container py-4 px-3 px-md-4">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">Corrige los siguientes errores:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0 pb-0">
                <h2 class="h6 text-white mb-0">Galeria de imagenes</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div id="galleryCarousel" class="carousel slide" data-bs-ride="false">
                    <div class="carousel-indicators" id="galleryIndicators"></div>
                    <div class="carousel-inner" id="galleryInner"></div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-gallery-images]');
        if (!trigger) {
            return;
        }

        event.preventDefault();

        let images = [];
        try {
            images = JSON.parse(trigger.getAttribute('data-gallery-images') || '[]');
        } catch (error) {
            images = [];
        }

        if (!Array.isArray(images) || images.length === 0) {
            return;
        }

        const startIndex = Number.parseInt(trigger.getAttribute('data-gallery-start') || '0', 10);
        const safeIndex = Number.isNaN(startIndex) ? 0 : Math.max(0, Math.min(startIndex, images.length - 1));

        const galleryInner = document.getElementById('galleryInner');
        const galleryIndicators = document.getElementById('galleryIndicators');
        const carouselElement = document.getElementById('galleryCarousel');
        const prevControl = carouselElement.querySelector('.carousel-control-prev');
        const nextControl = carouselElement.querySelector('.carousel-control-next');

        galleryInner.innerHTML = '';
        galleryIndicators.innerHTML = '';

        images.forEach(function (url, index) {
            const item = document.createElement('div');
            item.className = 'carousel-item' + (index === safeIndex ? ' active' : '');

            const image = document.createElement('img');
            image.src = url;
            image.className = 'd-block gallery-slide';
            image.alt = 'Imagen';
            item.appendChild(image);
            galleryInner.appendChild(item);

            const indicator = document.createElement('button');
            indicator.type = 'button';
            indicator.setAttribute('data-bs-target', '#galleryCarousel');
            indicator.setAttribute('data-bs-slide-to', String(index));
            indicator.setAttribute('aria-label', 'Slide ' + String(index + 1));
            if (index === safeIndex) {
                indicator.className = 'active';
                indicator.setAttribute('aria-current', 'true');
            }
            galleryIndicators.appendChild(indicator);
        });

        const showControls = images.length > 1;
        prevControl.classList.toggle('d-none', !showControls);
        nextControl.classList.toggle('d-none', !showControls);

        const carousel = bootstrap.Carousel.getOrCreateInstance(carouselElement, {
            interval: false,
            ride: false,
            touch: true
        });
        carousel.to(safeIndex);

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('galleryModal'));
        modal.show();
    });
</script>
</body>
</html>
