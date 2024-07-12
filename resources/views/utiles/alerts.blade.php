    {{-- @if (session()->has('success') || session()->has('message') || session()->has('error')) --}}
    @if (session()->has('success'))
        <div class="alert alert-success  py-2">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('message'))
        <div class="alert alert-success  py-2">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger  py-2">
            {{ session('error') }}
        </div>
    @endif
    @if (session()->has('noticia'))
        <div class="alert alert-warning  py-2">
            {{ session('noticia') }}
        </div>
    @endif

    {{-- @endif --}}
    @if ($errors->any())
        <div class="alert alert-danger  py-2">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <div id="alert-container"></div>
    
    @if (session()->has('error') || session()->has('noticia') || session()->has('message') 
            || session()->has('success') || $errors->any())
    <script>
        window.onload = function() {
            window.scrollTo(0, 0); // Mueve el cursor a la esquina superior del formulario
        };
    </script>
@endif
