// frontend/assets/js/fullcalendar-init.js

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: {
            url: '../backend/events.php',
            failure: function() {
                alert('There was an error while fetching events!');
            }
        },
        eventClick: function(info) {
            // Optionally show event details in a Bootstrap modal
            var event = info.event;
            var modalHtml = '<div class="modal fade" id="eventModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">' +
                '<div class="modal-header"><h5 class="modal-title">' + event.title + '</h5>' +
                '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>' +
                '<div class="modal-body">' +
                '<p><strong>Start:</strong> ' + event.start.toLocaleString() + '</p>' +
                (event.end ? '<p><strong>End:</strong> ' + event.end.toLocaleString() + '</p>' : '') +
                '</div></div></div></div>';
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            var modal = new bootstrap.Modal(document.getElementById('eventModal'));
            modal.show();
            document.getElementById('eventModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('eventModal').remove();
            });
        }
    });
    calendar.render();
}); 