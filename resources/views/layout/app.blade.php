<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>To-do-app</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
  <style>
    .active{
        font-weight: bold;
    }
  </style>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container-fluid">
        <a class="navbar-brand" href="{{url('/')}}">To-Do-App</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link " aria-current="page" href="{{url('/')}}">Home</a>
            </li>
            @auth
            <li class="nav-item">
                 <a class="nav-link {{ request()->is('posts*') ? 'active' : '' }}" href="{{ url('posts')}}">Posts</a>

            </li>
            @can('admin-control')

            <li class="nav-item">
                 <a class="nav-link {{ request()->is('users*') ? 'active' : '' }}" href="{{ url('users')}}">Users</a>

            </li>
            <li class="nav-item">
                 <a class="nav-link {{ request()->is('tags*') ? 'active' : '' }}" href="{{ url('tags')}}">Tags</a>

            </li>
            @endcan
            @endauth

          </ul>
          <form class="d-flex" role="search" method="GET" action="{{ url('posts/search') }}">
    <input class="form-control me-2" type="search" name="search" placeholder="Search" aria-label="Search" required/>
    <button class="btn btn-outline-success" type="submit">Search</button>
</form>
<ul class="navbar-nav ms-auto mb-2 mb-lg-0">
@guest
@if (Route::has('login'))
    <li class="nav-item">
        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
    </li>
@endif

@if (Route::has('register'))
    <li class="nav-item">
        <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
    </li>
@endif
@else
<li class="nav-item dropdown">
    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
        {{ Auth::user()->name }}
    </a>

    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
        <a class="dropdown-item" href="{{ route('logout') }}"
           onclick="event.preventDefault();
                         document.getElementById('logout-form').submit();">
            {{ __('Logout') }}
        </a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</li>
@endguest
</ul>
        </div>
      </div>
    </nav>

    <div class="container">
      <div class="row">
        @yield('content')

      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
  </body>
</html>
