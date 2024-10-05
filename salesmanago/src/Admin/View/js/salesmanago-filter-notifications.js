if (window.location.href.includes('admin.php?page=salesmanago')) {
    document.addEventListener('DOMContentLoaded', function () {
        // Select all WP notices
        let notices = document.querySelectorAll('.notice');
        //Remove non-salesmanago notices
        notices.forEach(function (notice) {
            if (!notice.classList.contains('salesmanago-notice')) {
                notice.parentNode.removeChild(notice);
            }
        });
    });
}