<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EVRent')</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        /* Custom Styles if needed */
        .glass {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans text-gray-900 antialiased">
    
    <!-- Navbar -->
    <nav class="bg-white shadow-lg fixed w-full z-50" x-data="{ isOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ url('/') }}" class="text-2xl font-bold text-blue-600">EVRent</a>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center">
                    @auth
                        <div class="ml-3 relative flex items-center gap-4">
                            @if(Auth::user()->role == 'pelanggan')
                                <a href="{{ route('katalog') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">Katalog</a>
                                <a href="{{ route('my_bookings') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">Pesanan Saya</a>
                            @endif
                            <div class="border-l pl-4 flex items-center gap-4">
                                <span class="text-sm font-semibold text-gray-800">{{ Auth::user()->username }} ({{ Auth::user()->role }})</span>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm">Logout</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="flex space-x-4">
                            <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                            <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">Register</a>
                        </div>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <div class="flex items-center md:hidden">
                    <button @click="isOpen = !isOpen" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg :class="{'hidden': isOpen, 'block': !isOpen }" class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg :class="{'block': isOpen, 'hidden': !isOpen }" class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="isOpen" class="md:hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                @auth
                    @if(Auth::user()->role == 'pelanggan')
                        <a href="{{ route('katalog') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Katalog</a>
                        <a href="{{ route('my_bookings') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Pesanan Saya</a>
                    @endif
                    <div class="border-t border-gray-200 pt-4 pb-3">
                        <div class="flex items-center px-5">
                            <div class="ml-3">
                                <div class="text-base font-medium leading-none text-gray-800">{{ Auth::user()->username }}</div>
                                <div class="text-sm font-medium leading-none text-gray-500 mt-1">{{ Auth::user()->email }}</div>
                            </div>
                        </div>
                        <div class="mt-3 px-2 space-y-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-red-600 hover:text-red-800 hover:bg-gray-50">Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Login</a>
                    <a href="{{ route('register') }}" class="block w-full text-center mt-4 px-4 py-3 bg-blue-600 font-bold text-white rounded-lg shadow hover:bg-blue-700">Register Sekarang</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="pt-20 min-h-screen">
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-10">
        <div class="container mx-auto text-center">
            <p>&copy; {{ date('Y') }} EVRent. All rights reserved.</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
