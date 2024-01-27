document.addEventListener('DOMContentLoaded', function() {
    // Get the side nav element
    var sideNav = document.querySelector('.sidenav');

    // Initialize the sidenav
    M.Sidenav.init(sideNav);

    // Handle click events on the sidenav-trigger
    document.querySelectorAll('.sidenav-trigger').forEach(function(trigger) {
        trigger.addEventListener('click', function() {
            var instance = M.Sidenav.getInstance(sideNav);
            instance.open();
        });
    });
});

function logout(sessionGuid) {
    // Voeg hier eventuele extra logout-logica toe, zoals het versturen van een AJAX-verzoek naar do_logout.php
    window.location.href = 'do_logout.php?session_guid=' + sessionGuid;
}

$(document).ready(function () {
	$('.selectcolumn').click(function () {
		var id = $(this).attr('id');
		if ($(this).is(":checked")) {
			$('.'+id).attr('checked', true);
		} else {
			$('.'+id).attr('checked', false);
		}
	});
});
