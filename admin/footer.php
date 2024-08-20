 

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    document.getElementById('toggleSidebar').addEventListener('click', function() {
        var sidebar = document.getElementById('sidebar');
        var content = document.getElementById('content');
        if (sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            content.classList.remove('shifted');
        } else {
            sidebar.classList.add('show');
            content.classList.add('shifted');
        }
    });
    function searchCourses(query) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('search', query);
        window.location.search = urlParams.toString();
    }
    function searchCourses() {
        const query = document.getElementById('search').value;
        const url = new URL(window.location.href);
        url.searchParams.set('search', query);
        window.location.href = url.href;
    }
</script>
</body>
</html>
