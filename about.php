<?php include 'header.php'; ?>

<header class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-brand-orange">About OASIS HMS</h1>
    <p class="text-sm text-gray-400">System Architecture & Development Timeline</p>
</header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    
    <section class="lg:col-span-2 rounded-lg border border-navy-700 bg-navy-900 p-6 shadow-sm">
        <h3 class="mb-6 font-bold text-brand-orange border-b border-navy-700 pb-2">Development Timeline</h3>
        
        <div class="relative border-l-2 border-brand-orange pl-6 ml-3 space-y-8">
            
            <div class="relative">
                <div class="absolute -left-[33px] top-1.5 h-4 w-4 rounded-full bg-brand-orange ring-4 ring-navy-900"></div>
                <h4 class="text-lg font-bold text-white mb-1">Phase 1: Database Design (ERD)</h4>
                <p class="text-sm text-gray-400 leading-relaxed">Designed the core entity-relationship diagram mapping out Customers, Rooms, Bookings, and complex trigger logic for pricing and maintenance. This architectural phase took extensive planning to ensure strict 3NF normalization.</p>
            </div>
            
            <div class="relative">
                <div class="absolute -left-[33px] top-1.5 h-4 w-4 rounded-full bg-brand-orange ring-4 ring-navy-900"></div>
                <h4 class="text-lg font-bold text-white mb-1">Phase 2: Backend Integration</h4>
                <p class="text-sm text-gray-400 leading-relaxed">Connected the MySQL engine using secure mysqli prepared statements. Built the foundational CRUD operations for dynamic bookings, room management, and staff directories.</p>
            </div>
            
            <div class="relative">
                <div class="absolute -left-[33px] top-1.5 h-4 w-4 rounded-full bg-brand-orange ring-4 ring-navy-900"></div>
                <h4 class="text-lg font-bold text-white mb-1">Phase 3: Smart Hub & Automation</h4>
                <p class="text-sm text-gray-400 leading-relaxed">Implemented advanced logic including AI room suggestion algorithms, automated complaint-to-ticket generation workflows, and dynamic surge pricing rules.</p>
            </div>
            
        </div>
    </section>

    <div class="space-y-8">
        <section class="rounded-lg border border-navy-700 bg-navy-900 p-6 shadow-sm">
            <h3 class="mb-4 font-bold text-brand-orange border-b border-navy-700 pb-2">Developer Profile</h3>
            <div class="space-y-3 text-sm">
                <p class="text-gray-400"><strong class="text-white w-24 inline-block">Developer:</strong> Abdul Rahman</p>
                <p class="text-gray-400"><strong class="text-white w-24 inline-block">Program:</strong> BSCS</p>
                <p class="text-gray-400"><strong class="text-white w-24 inline-block">Institution:</strong> UET Lahore</p>
            </div>
        </section>

        <section class="rounded-lg border border-navy-700 bg-navy-900 p-6 shadow-sm">
            <h3 class="mb-4 font-bold text-brand-orange border-b border-navy-700 pb-2">Tech Stack</h3>
            <ul class="space-y-3 text-sm text-gray-300">
                <li class="flex items-center gap-3 hover:text-white transition">
                    <div class="bg-navy-800 p-1.5 rounded border border-navy-700 text-brand-orange">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    </div>
                    Front-End: HTML5 & Tailwind CSS
                </li>
                <li class="flex items-center gap-3 hover:text-white transition">
                    <div class="bg-navy-800 p-1.5 rounded border border-navy-700 text-brand-blue">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    </div>
                    Back-End: PHP 8.x
                </li>
                <li class="flex items-center gap-3 hover:text-white transition">
                    <div class="bg-navy-800 p-1.5 rounded border border-navy-700 text-emerald-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/></svg>
                    </div>
                    Database: MySQL
                </li>
            </ul>
        </section>
    </div>

</div>

<?php include 'footer.php'; ?>