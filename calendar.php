<?php
$month = $_GET['month'] ?? date('n');
$year = $_GET['year'] ?? date('Y');
$month = max(1, min(12, (int)$month));
$year = (int)$year;

$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDay);
$startDay = date('w', $firstDay);
$monthName = date('F', $firstDay);

$prevMonth = $month - 1;
$prevYear = $year;
$nextMonth = $month + 1;
$nextYear = $year;

if ($prevMonth < 1) {
  $prevMonth = 12;
  $prevYear--;
}
if ($nextMonth > 12) {
  $nextMonth = 1;
  $nextYear++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Inquiry Calendar</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      background: #fff;
    }

    header, main {
      max-width: 800px;
      margin: auto;
    }

    header nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }

    h1 {
      margin: 0;
      font-size: 1.5rem;
      color: #80334d;
      text-align: center;
    }

    a.nav-link {
      text-decoration: none;
      color: #b76e79;
      font-weight: bold;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      width: 14.28%;
      height: 90px;
      border: 1px solid #eee;
      vertical-align: top;
      text-align: center;
      padding: 8px;
    }

    th {
      background-color: #f9f4f0;
      color: #80334d;
    }

    td.today {
      background-color: #ffe7ed;
      border: 2px solid #b76e79;
      font-weight: bold;
    }

    @media screen and (max-width: 700px) {
      th, td {
        padding: 6px;
        height: 70px;
        font-size: 14px;
      }

      h1 {
        font-size: 1.2rem;
      }
    }
  </style>
</head>
<body>

<header>
  <nav aria-label="Calendar Navigation">
    <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="nav-link">&laquo; Prev</a>
    <h1><?= $monthName . ' ' . $year ?></h1>
    <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="nav-link">Next &raquo;</a>
  </nav>
</header>

<main>
  <section aria-label="Monthly Calendar">
    <table>
      <thead>
        <tr>
          <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <?php
          $day = 1;
          for ($i = 0; $i < $startDay; $i++) echo "<td></td>";
          for ($i = $startDay; $i < 7; $i++) {
              $isToday = ($day == date('j') && $month == date('n') && $year == date('Y')) ? 'today' : '';
              echo "<td class='$isToday'>$day</td>";
              $day++;
          }
          echo "</tr>";

          while ($day <= $daysInMonth) {
              echo "<tr>";
              for ($i = 0; $i < 7 && $day <= $daysInMonth; $i++) {
                  $isToday = ($day == date('j') && $month == date('n') && $year == date('Y')) ? 'today' : '';
                  echo "<td class='$isToday'>$day</td>";
                  $day++;
              }
              while ($i < 7) {
                  echo "<td></td>";
                  $i++;
              }
              echo "</tr>";
          }
          ?>
      </tbody>
    </table>
  </section>
</main>

</body>
</html>
