                @if (session()->has('message') || session()->has('error')) 
                    @if (session()->has('message'))
                        <div class="alert alert-success ">
                            {{ session('message') }}
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger ">
                            {{ session('error') }}
                        </div>
                    @endif
                    <script>
                        $('.page-header-title').animate({
                                scrollTop: '0px'
                            }, 300);
                    </script>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger ">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif