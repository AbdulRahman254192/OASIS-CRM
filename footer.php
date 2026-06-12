</main> <!-- Closes Main Content Area -->
    </div> <!-- Closes Flex Wrapper -->

    <script>
        // Sidebar Toggle Logic for all pages
        document.addEventListener('DOMContentLoaded', () => {
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            const toggleSidebar = () => {
                sidebar.classList.toggle('-translate-x-full');
                if(sidebarOverlay) sidebarOverlay.classList.toggle('hidden');
            };
            
            if (menuToggle) menuToggle.addEventListener('click', toggleSidebar);
            if (sidebarOverlay) sidebarOverlay.addEventListener('click', toggleSidebar);
        });
    </script>
</body>
</html>