<?php
use App\Helpers\Helper;
use App\Models\Admin;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Companies;
use App\Models\Employees;
use App\Models\EmployeeType;

$controllerRoute = $module['controller_route'];
?>
<style>
  .lightbox .lb-nav {
      display: none !important;
  }
</style>
<style>
  * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
  }

  h1,
  h2,
  h3,
  h4,
  h5,
  p {
      padding: 0;
      margin: 0;
  }

  .stats-card {
      border: none;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      border-radius: 0.5rem;
  }

  .calendar-day {
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 0.25rem;
  }

  .calendar-day.present {
      background-color: #d4edda;
      color: #155724;
  }

  .calendar-day.absent {
      background-color: #f8d7da;
      color: #721c24;
  }

  .calendar-day.late {
      position: relative;
  }

  .calendar-day.late::after {
      content: "LATE";
      position: absolute;
      bottom: -15px;
      font-size: 0.75rem;
      color: #ff9800;
  }

  .calendar-day.week-off {
      background-color: #e2e3e5;
      color: #6c757d;
  }

  .employee_attendance_holder .card-header a {
      color: #000;
      font-size: 1.5rem;
      text-decoration: none;
      font-weight: 500;
  }

  .user_holder {
      margin: 20px 0 35px;
  }

  .attendance_info_holder .col-md-2 {
      width: 20%;
  }

  .present_box {
      background: #eff9f1;
      border-left: 5px solid #36c887;
  }

  .absent_box {
      background: #fcecec;
      border-left: 5px solid #e84b54;
  }

  .halfday_box {
      background: #fdf5ea;
      border-left: 5px solid #ecb82e;
  }

  .paidleave_box {
      background: #fdf1ff;
      border-left: 5px solid #d575d7;
  }

  .text-leave {
      color: #d575d7
  }

  .weekoff_box {
      background: #f3f3f3;
      border-left: 5px solid #898989;
  }

  .attendance_calender th,
  .attendance_calender td {
      text-align: center;
      padding: 0;
  }

  .attendance_calender table,
  .attendance_calender tbody,
  .attendance_calender td,
  .attendance_calender th,
  .attendance_calender thead,
  .attendance_calender tr {
      border: none
  }

  .attendance_calender td .cal_date {
    border-radius: 5px;
    color: #000;
    background: #f0f0f0; /* Default background */
    padding: 5px;
    height: 42px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    margin: 5px;
}

.attendance_calender td .cal_date.sunday {
    background: #8a8a8a; /* Highlight Sundays in grey */
    color: #fff;
}

.attendance_calender td .cal_date.before-today {
    background: #3ace78; /* Highlight dates before today in green */
    color: #fff;
}
.attendance_calender td .cal_date.default {
    background: #f0f0f0; /* Light grey or neutral background */
    color: #000; /* Neutral text color */
}

  .attendance_calender td:last-child .cal_date {
      margin-right: 0;
  }

  .cal_date p {
      text-transform: uppercase;
      line-height: 1;
      font-weight: 500;
  }

  .cal_date p+p {
      font-size: 10px;
  }
  /* modal css */
  .modal.drawer {
      display: flex !important;
      pointer-events: none;
  }

  .modal.drawer * {
      pointer-events: none;
  }

  .modal.drawer .modal-dialog {
      margin: 0px;
      display: flex;
      flex: auto;
      transform: translate(25%, 0);
  }

  .modal.drawer .modal-dialog .modal-content {
      border: none;
      border-radius: 0px;
  }

  .modal.drawer .modal-dialog .modal-content .modal-body {
      overflow: auto;
  }

  .modal.drawer.show {
      pointer-events: auto;
  }

  .modal.drawer.show * {
      pointer-events: auto;
  }

  .modal.drawer.show .modal-dialog {
      transform: translate(0, 0);
  }

  .modal.drawer.right-align {
      flex-direction: row-reverse;
  }

  .modal.drawer.left-align:not(.show) .modal-dialog {
      transform: translate(-25%, 0);
  }

  .status-buttons button {
      margin-right: 5px;
      font-size: 14px;
  }

  .leaves span {
      margin-right: 5px;
      font-size: 14px;
      padding: 5px 10px;
      border-radius: 20px;
      border: 1px solid;
      display: inline-block;
  }

  .leaves span.paid {
      background-color: #f8f9fa;
      color: purple;
      border-color: purple;
  }

  .leaves span.unpaid {
      background-color: #f8f9fa;
      color: blue;
      border-color: blue;
  }

  .leaves span.half {
      background-color: #f8f9fa;
      color: orange;
      border-color: orange;
  }

  .note {
      margin-top: 20px;
  }
  .table_user{
      width: 50px;
      height: 50px;
      border-radius: 50%;
      margin-right: 10px;
  }
  @media(max-width: 767px){
      .attendance_info_holder .col-md-2{
          width: 50%;
          margin-bottom: 15px;
      }
  }
</style>
<div class="pagetitle">
  <h1><?=$page_header?></h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Home</a></li>
      <li class="breadcrumb-item active"><?=$page_header?></li>
    </ol>
  </nav>
</div><!-- End Page Title -->
<section class="section">
    <div class="row justify-content-center">
        <!-- <div class="col-xl-12">
        @if(session('success_message'))
            <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show autohide" role="alert">
            {{ session('success_message') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error_message'))
            <div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show autohide" role="alert">
            {{ session('error_message') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        </div> -->
        <div class="employee_attendance_holder">
            <div class="container-fluid py-3">
                <!-- <header class="mb-4 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">KEYLINE DIGITECH PRIVATE LIMITED</h5>
                <span>Company Code: CN0459</span>
            </header> -->
                <div class="card">
                    <div class="card-header">
                        <a href="#"><i class="fa fa-arrow-left-long me-3"></i> Employee Attendance</a>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between user_holder">
                            <div class="d-flex align-items-center">
                                <img src="<?=env('UPLOADS_URL').$row->profile_image?>" class="rounded-circle me-3 table_user" alt="Employee">
                                <h5 class="mb-0"><?=$row->name?></h5><span class="badge rounded-pill bg-success ms-3">Present</span>
                            </div>
                            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 mt-md-0">
                            <div class="me-2">
                                <select class="form-select d-inline-block w-auto" id="monthSelect">
                                    <?php
                                    $currentMonth = date('m');
                                    for ($m = 1; $m <= 12; $m++) {
                                        $selected = ($m == $currentMonth) ? 'selected' : '';
                                        echo "<option value='{$m}' {$selected}>".date('F', mktime(0, 0, 0, $m, 1))."</option>";
                                    }
                                    ?>
                                </select>
                                <select class="form-select d-inline-block w-auto ms-2" id="yearSelect">
                                    <?php
                                    $currentYear = date('Y');
                                    $startYear = $currentYear - 5;
                                    $endYear = $currentYear + 5;

                                    for ($y = $startYear; $y <= $endYear; $y++) {
                                        $selected = ($y == $currentYear) ? 'selected' : '';
                                        echo "<option value='{$y}' {$selected}>{$y}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mt-2 mt-sm-0">
                                <button class="btn btn-primary">Download Report</button>
                            </div>
                            </div>
                        </div>
                        <div class="attendance_info_holder mb-4">
                            <div class="row mb-4">
                                <div class="col-md-2">
                                    <div class="card stats-card present_box">
                                        <div class="card-body">
                                            <h6 class="text-success">Present</h6>
                                            <h4 class="mb-0">20</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card stats-card absent_box">
                                        <div class="card-body">
                                            <h6 class="text-danger">Absent</h6>
                                            <h4 class="mb-0">5</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card stats-card halfday_box">
                                        <div class="card-body">
                                            <h6 class="text-warning">Half Day</h6>
                                            <h4 class="mb-0">0</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card stats-card paidleave_box">
                                        <div class="card-body">
                                            <h6 class="text-leave">Paid Leave</h6>
                                            <h4 class="mb-0">0.0</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card stats-card weekoff_box">
                                        <div class="card-body">
                                            <h6 class="text-muted">Week Off</h6>
                                            <h4 class="mb-0">5</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="attendance_calender pt-4">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sun</th>
                                    <th>Mon</th>
                                    <th>Tue</th>
                                    <th>Wed</th>
                                    <th>Thu</th>
                                    <th>Fri</th>
                                    <th>Sat</th>
                                </tr>                            
                                <tbody id="calendarBody">
                                <!-- Calendar Days will be inserted here dynamically -->            
                                </tbody>
                            </thead>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade drawer right-align" id="attendance_info_popup" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Edit Attendance:</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex align-items-center mb-3">
                            <div>
                                <h6 class="mb-0" id="modalDate"></h6>
                            </div>
                        </div>
                        <!-- <div class="status-buttons mb-3">
                            <button class="btn btn-outline-danger mb-1">ABSENT</button>
                            <button class="btn btn-outline-warning mb-1">HALF DAY</button>
                            <button class="btn btn-outline-success mb-1">PRESENT</button>
                            <button class="btn btn-outline-secondary mb-1">WEEK OFF</button>
                            <button class="btn btn-outline-info mb-1">HOLIDAY</button>
                        </div>
                        <div class="d-flex align-items-center mb-3 border-top pt-3">
                            <div>
                                <h6 class="mb-0">LEAVE</h6>
                            </div>
                        </div>
                        <div class="leaves mb-3">
                            <span class="paid m-1">PAID LEAVE</span>
                            <span class="half m-1">HALF DAY LEAVE</span>
                            <span class="unpaid m-1">UNPAID LEAVE</span>
                        </div> -->
                        <div class="mb-3">
                            <h6 class="mb-1">Punch Details</h6>
                            <!-- <div class="d-flex align-items-center mb-3">
                                <img src="5.png" alt="Profile" class="rounded-circle me-3 table_user">
                                <div>
                                    <p><strong>10:42 AM</strong> &middot; In</p>
                                    <small class="text-muted">45/30/1A, Surya Nagar Colony, Ashok Nagar, Tollygunge, Kolkata, West
                                    Bengal 700040, India</small>
                                </div>
                            </div> -->
                            <div id="punchDetails"></div>                        
                        </div>                    
                    </div>
                </div>
            </div>
        </div>
    </div>  
</section>
<script>
    const row = <?php echo $rowJson; ?>;
    // Step 1: Create a date-wise mapping of attendance data
    const attendanceData = {};
    row.forEach(item => {
        const date = item.attendance_date; 
        // Date of the attendance
        if (!attendanceData[date]) {
            attendanceData[date] = []; // If not already, initialize it as an empty array
        }
        attendanceData[date].push(item); // Add attendance data to that date
        // console.log(attendanceData);
    });

    // console.log(row); // To check if the data is passed correctly
    function generateCalendar(month, year) {
    // Get the first day of the month (0-6 where 0 = Sunday, 6 = Saturday)
    const firstDay = new Date(year, month - 1, 1).getDay();
    // Get the number of days in the month
    const daysInMonth = new Date(year, month, 0).getDate();
    const today = new Date(); // Current date
    // alert(today);

    let calendarHtml = '';
    let day = 1;

    // Loop through weeks (max 6 weeks)
    for (let week = 0; week < 6; week++) {
        calendarHtml += '<tr>';
        
        // Loop through days of the week (0=Sun, 6=Sat)
        for (let weekday = 0; weekday < 7; weekday++) {
            if (week === 0 && weekday < firstDay) {
                // Empty cells before the first day of the month
                calendarHtml += '<td></td>';
            } else if (day <= daysInMonth) {
                const currentDate = new Date(year, month - 1, day);
                const isBeforeToday = currentDate < today && currentDate.toDateString() !== today.toDateString();
                const isSunday = currentDate.getDay() === 0;                
                // console.log(today.toDateString());

                // Determine the class for the date
                let dateClass = 'cal_date';
                if (isSunday) {
                    // console.log(isSunday);
                    dateClass += ' sunday'; // Highlight Sundays in grey
                } else if (isBeforeToday) {
                    // console.log(isBeforeToday);
                    dateClass += ' before-today';// Highlight dates up to today in green
                } else if (today.toDateString() == currentDate.toDateString()) {
                    // console.log(isBeforeToday);
                    dateClass += ' before-today';// Highlight dates up to today in green
                } else {
                    dateClass += ' cal_date';
                }
                // Check if there's attendance data for this date
                const currentDateFormatted = currentDate.toISOString().split('T')[0]; // Format date to 'YYYY-MM-DD'
                const attendance = attendanceData[currentDateFormatted];
                // console.log(currentDateFormatted);
                
                // Add the day cell
                calendarHtml += `<td><div class="${dateClass} details-view" data-bs-toggle="modal" data-bs-target="#attendance_info_popup"><p>${day}</p></div></td>`;

                day++;
            } else {
                // Empty cells after the last day of the month
                calendarHtml += '<td></td>';
            }
        }
        calendarHtml += '</tr>';

        if (day > daysInMonth) break; // Break if all days are added
    }

    // Insert the generated calendar HTML into the table body
    document.getElementById('calendarBody').innerHTML = calendarHtml;
}

// Call the function with the current month and year
const currentDate = new Date();
generateCalendar(currentDate.getMonth() + 1, currentDate.getFullYear());


    // Event listeners for the select dropdowns
    document.getElementById('monthSelect').addEventListener('change', function() {
        const month = parseInt(this.value);
        const year = parseInt(document.getElementById('yearSelect').value);
        generateCalendar(month, year);
    });

    document.getElementById('yearSelect').addEventListener('change', function() {
        const month = parseInt(document.getElementById('monthSelect').value);
        const year = parseInt(this.value);
        generateCalendar(month, year);
    });

    // Initial calendar load
    generateCalendar(new Date().getMonth() + 1, new Date().getFullYear());

    // Add a click event listener to calendar cells
document.addEventListener("DOMContentLoaded", function () {
    const calendarCells = document.querySelectorAll(".cal_date");

    calendarCells.forEach(cell => {
        cell.addEventListener("click", function () {
            // Get the clicked cell's date
            const day = this.querySelector("p").innerText; // Extract the day number
            const month = currentDate.getMonth() + 1; // Current month (adjusted for 0-based index)
            const year = currentDate.getFullYear(); // Current year

            // Format the date as YYYY-MM-DD
            const selectedDate = `${year}-${month.toString().padStart(2, '0')}-${day.padStart(2, '0')}`;
            // console.log(selectedDate);

            // Fetch attendance data for the selected date
            if (attendanceData[selectedDate]) {
                // console.log(`Attendance data for ${selectedDate}:`, attendanceData[selectedDate]);
                // Open the modal and populate it with data
                openAttendanceModal(selectedDate, attendanceData[selectedDate]);
            } else {
                // console.log(`No attendance data for ${selectedDate}`);
                alert(`No attendance data available for ${selectedDate}`);
            }
        });
    });
});

// Function to open the modal and populate it
function openAttendanceModal(date, data) {
    // Find the modal for the selected date
    // const modalId = `attendance_info_popup${date.replace(/-/g, '')}`; // Convert date to match modal ID format
    const modal = document.getElementById("attendance_info_popup");
    const modalInstance = new bootstrap.Modal(modal);
    document.getElementById("modalTitle").innerText = `Edit Attendance for ${date}`;
    document.getElementById("modalDate").innerText = date;
    const punchDetailsContainer = document.getElementById("punchDetails");
    punchDetailsContainer.innerHTML = ""; // Clear existing punch details

    data.forEach(item => {
        const punchDiv = document.createElement("div");
        punchDiv.classList.add("d-flex", "align-items-start", "flex-column", "border-bottom", "border-success", "pb-3", "mb-3");

        const startDiv = document.createElement("div");
    startDiv.classList.add("d-flex", "align-items-center", "me-3");

    startDiv.innerHTML = `
            <img src="${item.start_image}" alt="Profile" class="rounded-circle me-3 table_user">
            <div>
                <p><strong>${formatTime(item.start_timestamp) || "N/A"}</strong> &middot; In</p>
                <small class="text-muted">${item.start_address || "No location available"}</small>
            </div>            
        `;
        const endDiv = document.createElement("div");
    endDiv.classList.add("d-flex", "align-items-center");

    endDiv.innerHTML = `
        <img src="${item.end_image}" alt="Profile" class="rounded-circle me-3 table_user">
        <div>
            <p><strong>${formatTime(item.end_timestamp) || "N/A"}</strong> &middot; Out</p>
            <small class="text-muted">${item.end_address || "No location available"}</small>
        </div>
    `;
    punchDiv.appendChild(startDiv);
    punchDiv.appendChild(endDiv);
        punchDetailsContainer.appendChild(punchDiv);
    });

    
    modalInstance.show();
    // Cleanup: Ensure backdrop is removed when modal is hidden
    modal.addEventListener('hidden.bs.modal', function () {
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();  // Manually remove the backdrop if it still exists
        }
    });   
}
function formatTime(timestamp) {
    return timestamp ? new Date(timestamp).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }) : "N/A";
}
</script>
<script>
    // Function to handle the month/year change and update the calendar via AJAX
    document.getElementById('monthSelect').addEventListener('change', function() {
        const month = parseInt(this.value);
        const year = parseInt(document.getElementById('yearSelect').value);

        updateCalendar(month, year);
    });

    document.getElementById('yearSelect').addEventListener('change', function() {
        const month = parseInt(document.getElementById('monthSelect').value);
        const year = parseInt(this.value);

        updateCalendar(month, year);
    });

    function updateCalendar(month, year) {
        fetch("{{ route('attendance.updateCalendar') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({ month, year })
        })
        .then(response => response.json())
        .then(data => {
            // Update the calendar body with the new calendar HTML
            document.getElementById('calendarBody').innerHTML = data.calendarHtml;
        });
    }
</script>
