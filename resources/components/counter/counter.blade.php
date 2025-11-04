<div class="flex items-center justify-center bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 p-4">
    <div class="relative group">
        <!-- Main Card -->
        <div class="relative bg-white rounded-2xl shadow-2xl shadow-blue-500/20 p-12 border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-3xl hover:shadow-blue-500/30 hover:scale-[1.02]">
            <!-- Gradient Background Overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-indigo-500/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-400/10 to-purple-400/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-indigo-400/10 to-pink-400/10 rounded-full blur-2xl"></div>
            
            <!-- Content -->
            <div class="relative z-10 text-center">
                <!-- Label -->
                <div class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">
                    Counter
                </div>
                
                <!-- Number Display -->
                <div class="text-8xl font-bold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2 tabular-nums">
                    {{ $count }}
                </div>
                
                <!-- Decorative Line -->
                <div class="w-20 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 rounded-full mx-auto mt-4"></div>
            </div>
            
            <!-- Shine Effect on Hover -->
            <div class="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-1000 bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
        </div>
        
        <!-- Glow Effect -->
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 via-indigo-500/20 to-purple-500/20 rounded-2xl blur-xl -z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    </div>
</div>