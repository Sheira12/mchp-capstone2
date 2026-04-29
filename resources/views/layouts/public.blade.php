<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Welcome') — {{ config('parish.name') }}</title>
    <meta name="description" content="@yield('meta-description', 'Mary Help of Christians Parish - Southville 1, Niugan, Cabuyao, Laguna')">
    <link rel="icon" type="image/png" href="{{ asset('images/parish-logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-white text-gray-800">

    {{-- Navigation --}}
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/parish-logo.png') }}" alt="Parish Logo" class="w-10 h-10 rounded-full object-cover">
                    <div class="hidden sm:block leading-tight">
                        <p class="text-sm font-bold text-blue-900">Mary Help of Christians Parish</p>
                        <p class="text-xs text-gray-500">Southville 1, Niugan, Cabuyao, Laguna</p>
                    </div>
                </a>

                {{-- Desktop Nav --}}
                <div class="hidden md:flex items-center gap-6">
                    <a href="{{ route('home') }}" class="nav-public {{ request()->routeIs('home') ? 'text-blue-700 font-semibold' : 'text-gray-600 hover:text-blue-700' }}">Home</a>
                    <a href="{{ route('about') }}" class="nav-public {{ request()->routeIs('about') ? 'text-blue-700 font-semibold' : 'text-gray-600 hover:text-blue-700' }}">About</a>
                    <a href="{{ route('services') }}" class="nav-public {{ request()->routeIs('services') ? 'text-blue-700 font-semibold' : 'text-gray-600 hover:text-blue-700' }}">Services</a>
                    <a href="{{ route('announcements') }}" class="nav-public {{ request()->routeIs('announcements*') ? 'text-blue-700 font-semibold' : 'text-gray-600 hover:text-blue-700' }}">Announcements</a>
                    <a href="{{ route('contact') }}" class="nav-public {{ request()->routeIs('contact') ? 'text-blue-700 font-semibold' : 'text-gray-600 hover:text-blue-700' }}">Contact</a>
                </div>

                {{-- Auth buttons --}}
                <div class="flex items-center gap-3">
                    @auth
                        @if(auth()->user()->hasRole(['super_admin', 'parish_secretary', 'finance_officer']))
                            <a href="{{ route('admin.dashboard') }}" class="btn-primary text-sm">Admin Panel</a>
                        @else
                            <a href="{{ route('parishioner.dashboard') }}" class="btn-primary text-sm">My Portal</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-blue-700">Login</a>
                        <a href="{{ route('register') }}" class="btn-primary text-sm">Register</a>
                    @endauth

                    {{-- Mobile menu button --}}
                    <button id="mobile-menu-btn" class="md:hidden text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div id="mobile-menu" class="hidden md:hidden border-t bg-white">
            <div class="px-4 py-3 space-y-2">
                <a href="{{ route('home') }}" class="block py-2 text-gray-700 hover:text-blue-700">Home</a>
                <a href="{{ route('about') }}" class="block py-2 text-gray-700 hover:text-blue-700">About</a>
                <a href="{{ route('services') }}" class="block py-2 text-gray-700 hover:text-blue-700">Services</a>
                <a href="{{ route('announcements') }}" class="block py-2 text-gray-700 hover:text-blue-700">Announcements</a>
                <a href="{{ route('contact') }}" class="block py-2 text-gray-700 hover:text-blue-700">Contact</a>
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-green-50 border-b border-green-200 text-green-800 px-4 py-3 text-sm text-center">
            {{ session('success') }}
        </div>
    @endif

    {{-- Page content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-blue-900 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('images/parish-logo.png') }}" alt="Logo" class="w-12 h-12 rounded-full object-cover">
                        <div>
                            <p class="font-bold">Mary Help of Christians Parish</p>
                            <p class="text-blue-300 text-sm">Diocese of San Pablo</p>
                        </div>
                    </div>
                    <p class="text-blue-200 text-sm">Serving the community of Southville 1, Niugan, Cabuyao, Laguna with faith, hope, and love.</p>
                </div>
                <div>
                    <h3 class="font-semibold mb-3">Quick Links</h3>
                    <ul class="space-y-2 text-blue-200 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-white">Home</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-white">About the Parish</a></li>
                        <li><a href="{{ route('services') }}" class="hover:text-white">Services & Sacraments</a></li>
                        <li><a href="{{ route('announcements') }}" class="hover:text-white">Announcements</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold mb-3">Contact Information</h3>
                    <ul class="space-y-2 text-blue-200 text-sm">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Southville 1, Niugan, Cabuyao, Laguna
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ config('parish.phone') }}
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ config('parish.email') }}
                        </li>
                    </ul>
                    <div class="mt-4">
                        <p class="text-sm font-medium mb-1">Office Hours</p>
                        <p class="text-blue-200 text-sm">Mon–Fri: 8AM–5PM</p>
                        <p class="text-blue-200 text-sm">Sat: 8AM–12PM</p>
                    </div>
                </div>
            </div>
            <div class="border-t border-blue-800 mt-8 pt-6 text-center text-blue-300 text-sm">
                © {{ date('Y') }} Mary Help of Christians Parish. All rights reserved.
            </div>
        </div>
    </footer>

    {{-- Chatbot Widget --}}
    <div id="chatbot-widget" class="fixed bottom-6 right-6 z-50">
        <button id="chatbot-toggle" class="w-14 h-14 bg-blue-700 hover:bg-blue-800 text-white rounded-full shadow-lg flex items-center justify-center transition-all">
            <svg id="chat-icon" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            <svg id="close-icon" class="w-7 h-7 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div id="chatbot-panel" class="hidden absolute bottom-16 right-0 w-80 bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden" style="height: 450px;">
            <div class="bg-blue-700 text-white px-4 py-3 flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-sm">Parish Assistant</p>
                    <p class="text-xs text-blue-200">Online</p>
                </div>
            </div>

            <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
                <div class="bot-message">
                    <div class="bg-white rounded-2xl rounded-tl-none px-3 py-2 text-sm shadow-sm max-w-xs">
                        Hello! Welcome to Mary Help of Christians Parish. How can I help you today?
                    </div>
                </div>
            </div>

            <div class="p-3 border-t bg-white">
                <div class="flex gap-2">
                    <input id="chat-input" type="text" placeholder="Type a message..." class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:border-blue-500">
                    <button id="chat-send" class="w-9 h-9 bg-blue-700 text-white rounded-full flex items-center justify-center hover:bg-blue-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </div>
                <button id="chat-escalate" class="mt-2 w-full text-xs text-blue-600 hover:underline">Talk to parish staff</button>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu
        document.getElementById('mobile-menu-btn')?.addEventListener('click', () => {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Chatbot
        const sessionId = 'chat_' + Math.random().toString(36).substr(2, 9);
        const toggle    = document.getElementById('chatbot-toggle');
        const panel     = document.getElementById('chatbot-panel');
        const chatIcon  = document.getElementById('chat-icon');
        const closeIcon = document.getElementById('close-icon');
        const messages  = document.getElementById('chat-messages');
        const input     = document.getElementById('chat-input');
        const sendBtn   = document.getElementById('chat-send');
        const escalate  = document.getElementById('chat-escalate');

        toggle.addEventListener('click', () => {
            panel.classList.toggle('hidden');
            chatIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        });

        function addMessage(text, sender) {
            const div = document.createElement('div');
            div.className = sender === 'user' ? 'flex justify-end' : 'bot-message';
            const bubble = document.createElement('div');
            bubble.className = sender === 'user'
                ? 'bg-blue-700 text-white rounded-2xl rounded-tr-none px-3 py-2 text-sm max-w-xs'
                : 'bg-white rounded-2xl rounded-tl-none px-3 py-2 text-sm shadow-sm max-w-xs';
            bubble.innerHTML = text.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            div.appendChild(bubble);
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
        }

        async function sendMessage() {
            const text = input.value.trim();
            if (!text) return;
            input.value = '';
            addMessage(text, 'user');

            try {
                const res = await fetch('{{ route("chatbot.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ message: text, session_id: sessionId }),
                });
                const data = await res.json();
                addMessage(data.message, 'bot');
            } catch {
                addMessage('Sorry, I encountered an error. Please try again.', 'bot');
            }
        }

        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keypress', e => e.key === 'Enter' && sendMessage());

        escalate.addEventListener('click', async () => {
            const lastMsg = input.value || 'User requested staff assistance';
            try {
                const res = await fetch('{{ route("chatbot.escalate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ session_id: sessionId, message: lastMsg }),
                });
                const data = await res.json();
                addMessage(data.message, 'bot');
            } catch {
                addMessage('Unable to connect to staff. Please call our office directly.', 'bot');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
