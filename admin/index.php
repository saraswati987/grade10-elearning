

<?php

include "./includes/header.php";
include "./includes/sidebar.php";
include __DIR__ . "/../config/db.php";

// --- Fetch summary stats ---
$totalStudents      = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalTeachers      = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
$totalSubjects      = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
$totalMaterials     = $pdo->query("SELECT COUNT(*) FROM materials")->fetchColumn();
$totalAssignments   = $pdo->query("SELECT COUNT(*) FROM assignments")->fetchColumn();
$totalSubmissions   = $pdo->query("SELECT COUNT(*) FROM submissions")->fetchColumn();
$pendingSubmissions = $pdo->query("SELECT COUNT(*) FROM submissions WHERE grade = 'Pending'")->fetchColumn();
$overdueAssignments = $pdo->query("SELECT COUNT(*) FROM assignments WHERE due_date < CURDATE()")->fetchColumn();

// --- Recent students ---
$recentStudents = $pdo->query(
    "SELECT name, email, roll_no, created_at FROM users WHERE role = 'student' ORDER BY created_at DESC LIMIT 5"
)->fetchAll();

// --- Recent submissions ---
$recentSubmissions = $pdo->query(
    "SELECT s.submitted_at, s.grade, u.name AS student_name, a.title AS assignment_title
     FROM submissions s
     JOIN users u ON s.student_id = u.id
     JOIN assignments a ON s.assignment_id = a.id
     ORDER BY s.submitted_at DESC
     LIMIT 6"
)->fetchAll();

// --- Assignments with due dates ---
$upcomingAssignments = $pdo->query(
    "SELECT a.title, a.due_date, sub.name AS subject_name,
            (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id) AS submission_count
     FROM assignments a
     JOIN subjects sub ON a.subject_id = sub.id
     ORDER BY a.due_date ASC
     LIMIT 5"
)->fetchAll();

// --- Subjects with material and assignment counts ---
$subjectsWithCounts = $pdo->query(
    "SELECT s.name, s.code,
            (SELECT COUNT(*) FROM materials WHERE subject_id = s.id) AS material_count,
            (SELECT COUNT(*) FROM assignments WHERE subject_id = s.id) AS assignment_count
     FROM subjects s
     ORDER BY s.name ASC"
)->fetchAll();

?>
        <!-- ===== Main Content Start ===== -->
        <main>
          <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

            <!-- Page Title -->
            <div class="mb-6">
              <h2 class="text-2xl font-bold text-black dark:text-white">Dashboard</h2>
              <p class="text-sm text-body">Overview of your learning management system</p>
            </div>

            <!-- ===== Stats Cards ===== -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5 mb-6">

              <!-- Students Card -->
              <div class="rounded-sm border border-stroke bg-white px-7.5 py-6 shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
                  <svg class="fill-primary dark:fill-white" width="22" height="18" viewBox="0 0 22 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.18418 8.03751C9.31543 8.03751 11.0686 6.35313 11.0686 4.25626C11.0686 2.15938 9.31543 0.475006 7.18418 0.475006C5.05293 0.475006 3.2998 2.15938 3.2998 4.25626C3.2998 6.35313 5.05293 8.03751 7.18418 8.03751ZM7.18418 2.05626C8.45605 2.05626 9.52168 3.05313 9.52168 4.29063C9.52168 5.52813 8.49043 6.52501 7.18418 6.52501C5.87793 6.52501 4.84668 5.52813 4.84668 4.29063C4.84668 3.05313 5.9123 2.05626 7.18418 2.05626Z" fill=""/>
                    <path d="M15.8124 9.6875C17.6687 9.6875 19.1468 8.24375 19.1468 6.42188C19.1468 4.6 17.6343 3.15625 15.8124 3.15625C13.9905 3.15625 12.478 4.6 12.478 6.42188C12.478 8.24375 13.9905 9.6875 15.8124 9.6875ZM15.8124 4.7375C16.8093 4.7375 17.5999 5.49375 17.5999 6.45625C17.5999 7.41875 16.8093 8.175 15.8124 8.175C14.8155 8.175 14.0249 7.41875 14.0249 6.45625C14.0249 5.49375 14.8155 4.7375 15.8124 4.7375Z" fill=""/>
                    <path d="M15.9843 10.0313H15.6749C14.6437 10.0313 13.6468 10.3406 12.7874 10.8563C11.8593 9.61876 10.3812 8.79376 8.73115 8.79376H5.67178C2.85303 8.82814 0.618652 11.0625 0.618652 13.8469V16.3219C0.618652 16.975 1.13428 17.4906 1.7874 17.4906H20.2468C20.8999 17.4906 21.4499 16.9406 21.4499 16.2875V15.4625C21.4155 12.4719 18.9749 10.0313 15.9843 10.0313ZM2.16553 15.9438V13.8469C2.16553 11.9219 3.74678 10.3406 5.67178 10.3406H8.73115C10.6562 10.3406 12.2374 11.9219 12.2374 13.8469V15.9438H2.16553V15.9438ZM19.8687 15.9438H13.7499V13.8469C13.7499 13.2969 13.6468 12.7469 13.4749 12.2313C14.0937 11.7844 14.8499 11.5781 15.6405 11.5781H15.9499C18.0812 11.5781 19.8343 13.3313 19.8343 15.4625V15.9438H19.8687Z" fill=""/>
                  </svg>
                </div>
                <div class="mt-4 flex items-end justify-between">
                  <div>
                    <h4 class="text-title-md font-bold text-black dark:text-white"><?= $totalStudents ?></h4>
                    <span class="text-sm font-medium">Total Students</span>
                  </div>
                </div>
              </div>

              <!-- Subjects Card -->
              <div class="rounded-sm border border-stroke bg-white px-7.5 py-6 shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
                  <svg class="fill-primary dark:fill-white" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M21.1063 18.0469L19.3875 3.23126C19.2157 1.71876 17.9438 0.584381 16.3969 0.584381H5.56878C4.05628 0.584381 2.78441 1.71876 2.57816 3.23126L0.859406 18.0469C0.756281 18.9063 1.03128 19.7313 1.61566 20.3844C2.20003 21.0375 2.99066 21.3813 3.85003 21.3813H18.1157C18.975 21.3813 19.8 21.0031 20.35 20.3844C20.9 19.7656 21.2094 18.9063 21.1063 18.0469ZM19.2157 19.3531C18.9407 19.6625 18.5625 19.8344 18.15 19.8344H3.85003C3.43753 19.8344 3.05941 19.6625 2.78441 19.3531C2.50941 19.0438 2.37191 18.6313 2.44066 18.2188L4.12503 3.43751C4.19378 2.71563 4.81253 2.16563 5.56878 2.16563H16.4313C17.1532 2.16563 17.7719 2.71563 17.875 3.43751L19.5938 18.2531C19.6282 18.6656 19.4907 19.0438 19.2157 19.3531Z" fill=""/>
                    <path d="M14.3345 5.29375C13.922 5.39688 13.647 5.80938 13.7501 6.22188C13.7845 6.42813 13.8189 6.63438 13.8189 6.80625C13.8189 8.35313 12.547 9.625 11.0001 9.625C9.45327 9.625 8.1814 8.35313 8.1814 6.80625C8.1814 6.6 8.21577 6.42813 8.25015 6.22188C8.35327 5.80938 8.07827 5.39688 7.66577 5.29375C7.25327 5.19063 6.84077 5.46563 6.73765 5.87813C6.6689 6.1875 6.63452 6.49688 6.63452 6.80625C6.63452 9.2125 8.5939 11.1719 11.0001 11.1719C13.4064 11.1719 15.3658 9.2125 15.3658 6.80625C15.3658 6.49688 15.3314 6.1875 15.2626 5.87813C15.1595 5.46563 14.747 5.225 14.3345 5.29375Z" fill=""/>
                  </svg>
                </div>
                <div class="mt-4 flex items-end justify-between">
                  <div>
                    <h4 class="text-title-md font-bold text-black dark:text-white"><?= $totalSubjects ?></h4>
                    <span class="text-sm font-medium">Total Subjects</span>
                  </div>
                  <a href="course.php" class="text-xs text-primary hover:underline">Manage</a>
                </div>
              </div>

              <!-- Assignments Card -->
              <div class="rounded-sm border border-stroke bg-white px-7.5 py-6 shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
                  <svg class="fill-primary dark:fill-white" width="20" height="22" viewBox="0 0 20 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11.7531 16.4312C10.3781 16.4312 9.27808 17.5312 9.27808 18.9062C9.27808 20.2812 10.3781 21.3812 11.7531 21.3812C13.1281 21.3812 14.2281 20.2812 14.2281 18.9062C14.2281 17.5656 13.0937 16.4312 11.7531 16.4312ZM11.7531 19.8687C11.2375 19.8687 10.825 19.4562 10.825 18.9406C10.825 18.425 11.2375 18.0125 11.7531 18.0125C12.2687 18.0125 12.6812 18.425 12.6812 18.9406C12.6812 19.4219 12.2343 19.8687 11.7531 19.8687Z" fill=""/>
                    <path d="M5.22183 16.4312C3.84683 16.4312 2.74683 17.5312 2.74683 18.9062C2.74683 20.2812 3.84683 21.3812 5.22183 21.3812C6.59683 21.3812 7.69683 20.2812 7.69683 18.9062C7.69683 17.5656 6.56245 16.4312 5.22183 16.4312ZM5.22183 19.8687C4.7062 19.8687 4.2937 19.4562 4.2937 18.9406C4.2937 18.425 4.7062 18.0125 5.22183 18.0125C5.73745 18.0125 6.14995 18.425 6.14995 18.9406C6.14995 19.4219 5.73745 19.8687 5.22183 19.8687Z" fill=""/>
                    <path d="M19.0062 0.618744H17.15C16.325 0.618744 15.6031 1.23749 15.5 2.06249L14.95 6.01562H1.37185C1.0281 6.01562 0.684353 6.18749 0.443728 6.46249C0.237478 6.73749 0.134353 7.11562 0.237478 7.45937C0.237478 7.49374 0.237478 7.49374 0.237478 7.52812L2.36873 13.9562C2.50623 14.4375 2.9531 14.7812 3.46873 14.7812H12.9562C14.2281 14.7812 15.3281 13.8187 15.5 12.5469L16.9437 2.26874C16.9437 2.19999 17.0125 2.16562 17.0812 2.16562H18.9375C19.35 2.16562 19.7281 1.82187 19.7281 1.37499C19.7281 0.928119 19.4187 0.618744 19.0062 0.618744ZM14.0219 12.3062C13.9531 12.8219 13.5062 13.2 12.9906 13.2H3.7781L1.92185 7.56249H14.7094L14.0219 12.3062Z" fill=""/>
                  </svg>
                </div>
                <div class="mt-4 flex items-end justify-between">
                  <div>
                    <h4 class="text-title-md font-bold text-black dark:text-white"><?= $totalAssignments ?></h4>
                    <span class="text-sm font-medium">Total Assignments</span>
                  </div>
                  <span class="text-xs text-meta-1"><?= $overdueAssignments ?> overdue</span>
                </div>
              </div>

              <!-- Submissions Card -->
              <div class="rounded-sm border border-stroke bg-white px-7.5 py-6 shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
                  <svg class="fill-primary dark:fill-white" width="22" height="16" viewBox="0 0 22 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11 15.1156C4.19376 15.1156 0.825012 8.61876 0.687512 8.34376C0.584387 8.13751 0.584387 7.86251 0.687512 7.65626C0.825012 7.38126 4.19376 0.918762 11 0.918762C17.8063 0.918762 21.175 7.38126 21.3125 7.65626C21.4156 7.86251 21.4156 8.13751 21.3125 8.34376C21.175 8.61876 17.8063 15.1156 11 15.1156ZM2.26876 8.00001C3.02501 9.27189 5.98126 13.5688 11 13.5688C16.0188 13.5688 18.975 9.27189 19.7313 8.00001C18.975 6.72814 16.0188 2.43126 11 2.43126C5.98126 2.43126 3.02501 6.72814 2.26876 8.00001Z" fill=""/>
                    <path d="M11 10.9219C9.38438 10.9219 8.07812 9.61562 8.07812 8C8.07812 6.38438 9.38438 5.07812 11 5.07812C12.6156 5.07812 13.9219 6.38438 13.9219 8C13.9219 9.61562 12.6156 10.9219 11 10.9219ZM11 6.625C10.2437 6.625 9.625 7.24375 9.625 8C9.625 8.75625 10.2437 9.375 11 9.375C11.7563 9.375 12.375 8.75625 12.375 8C12.375 7.24375 11.7563 6.625 11 6.625Z" fill=""/>
                  </svg>
                </div>
                <div class="mt-4 flex items-end justify-between">
                  <div>
                    <h4 class="text-title-md font-bold text-black dark:text-white"><?= $totalSubmissions ?></h4>
                    <span class="text-sm font-medium">Total Submissions</span>
                  </div>
                  <span class="text-xs <?= $pendingSubmissions > 0 ? 'text-meta-6' : 'text-meta-3' ?>"><?= $pendingSubmissions ?> pending</span>
                </div>
              </div>

            </div>
            <!-- ===== Stats Cards End ===== -->

            <!-- ===== Secondary Stats Row ===== -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3 md:gap-6 mb-6">

              <div class="rounded-sm border border-stroke bg-white px-6 py-4 shadow-default dark:border-strokedark dark:bg-boxdark flex items-center gap-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4 flex-shrink-0">
                  <svg class="fill-primary dark:fill-white" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 5C13.66 5 15 6.34 15 8C15 9.66 13.66 11 12 11C10.34 11 9 9.66 9 8C9 6.34 10.34 5 12 5ZM12 19.2C9.5 19.2 7.29 17.92 6 15.98C6.03 13.99 10 12.9 12 12.9C13.99 12.9 17.97 13.99 18 15.98C16.71 17.92 14.5 19.2 12 19.2Z" fill=""/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-bodydark2">Teachers</p>
                  <h4 class="text-xl font-bold text-black dark:text-white"><?= $totalTeachers ?></h4>
                </div>
              </div>

              <div class="rounded-sm border border-stroke bg-white px-6 py-4 shadow-default dark:border-strokedark dark:bg-boxdark flex items-center gap-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4 flex-shrink-0">
                  <svg class="fill-primary dark:fill-white" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2ZM16 18H8V16H16V18ZM16 14H8V12H16V14ZM13 9V3.5L18.5 9H13Z" fill=""/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-bodydark2">Study Materials</p>
                  <h4 class="text-xl font-bold text-black dark:text-white"><?= $totalMaterials ?></h4>
                </div>
              </div>

              <div class="rounded-sm border border-stroke bg-white px-6 py-4 shadow-default dark:border-strokedark dark:bg-boxdark flex items-center gap-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4 flex-shrink-0">
                  <svg class="fill-meta-3" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 16.17L4.83 12L3.41 13.41L9 19L21 7L19.59 5.59L9 16.17Z" fill=""/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-bodydark2">Graded Submissions</p>
                  <h4 class="text-xl font-bold text-black dark:text-white"><?= $totalSubmissions - $pendingSubmissions ?></h4>
                </div>
              </div>

            </div>
            <!-- ===== Secondary Stats Row End ===== -->

            <!-- ===== Two-column layout ===== -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 2xl:gap-7.5 mb-6">

              <!-- Recent Submissions -->
              <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="border-b border-stroke px-6 py-4 dark:border-strokedark">
                  <h3 class="font-semibold text-black dark:text-white">Recent Submissions</h3>
                </div>
                <div class="p-4">
                  <?php if (empty($recentSubmissions)): ?>
                    <p class="text-sm text-body py-4 text-center">No submissions yet.</p>
                  <?php else: ?>
                    <div class="flex flex-col gap-3">
                      <?php foreach ($recentSubmissions as $sub): ?>
                        <div class="flex items-center justify-between border-b border-stroke pb-3 dark:border-strokedark last:border-0 last:pb-0">
                          <div>
                            <p class="text-sm font-medium text-black dark:text-white"><?= htmlspecialchars($sub['student_name']) ?></p>
                            <p class="text-xs text-body"><?= htmlspecialchars($sub['assignment_title']) ?></p>
                          </div>
                          <div class="text-right">
                            <?php
                              $grade = $sub['grade'];
                              if ($grade === 'Pending') {
                                  $badgeClass = 'bg-warning bg-opacity-10 text-warning';
                              } elseif (in_array($grade, ['A+', 'A', 'A-', 'B+', 'B'])) {
                                  $badgeClass = 'bg-meta-3 bg-opacity-10 text-meta-3';
                              } elseif (in_array($grade, ['C', 'D'])) {
                                  $badgeClass = 'bg-meta-6 bg-opacity-10 text-meta-6';
                              } elseif ($grade === 'F') {
                                  $badgeClass = 'bg-meta-1 bg-opacity-10 text-meta-1';
                              } else {
                                  $badgeClass = 'bg-meta-2 text-black dark:text-white';
                              }
                            ?>
                            <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium <?= $badgeClass ?>"><?= htmlspecialchars($grade) ?></span>
                            <p class="text-xs text-body mt-0.5"><?= date('M d', strtotime($sub['submitted_at'])) ?></p>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Assignments Overview -->
              <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="border-b border-stroke px-6 py-4 dark:border-strokedark flex items-center justify-between">
                  <h3 class="font-semibold text-black dark:text-white">Assignments</h3>
                  <a href="/grade10-elearning/manage_assignments.php" class="text-xs text-primary hover:underline">View all</a>
                </div>
                <div class="p-4">
                  <?php if (empty($upcomingAssignments)): ?>
                    <p class="text-sm text-body py-4 text-center">No assignments yet.</p>
                  <?php else: ?>
                    <div class="flex flex-col gap-3">
                      <?php foreach ($upcomingAssignments as $asgn): ?>
                        <?php
                          $dueDate  = strtotime($asgn['due_date']);
                          $today    = strtotime(date('Y-m-d'));
                          $isOverdue  = $dueDate < $today;
                          $isDueSoon  = !$isOverdue && ($dueDate - $today) <= (3 * 86400);
                        ?>
                        <div class="flex items-center justify-between border-b border-stroke pb-3 dark:border-strokedark last:border-0 last:pb-0">
                          <div>
                            <p class="text-sm font-medium text-black dark:text-white"><?= htmlspecialchars($asgn['title']) ?></p>
                            <p class="text-xs text-body"><?= htmlspecialchars($asgn['subject_name']) ?></p>
                          </div>
                          <div class="text-right">
                            <?php if ($isOverdue): ?>
                              <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium bg-meta-1 bg-opacity-10 text-meta-1">Overdue</span>
                            <?php elseif ($isDueSoon): ?>
                              <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium bg-warning bg-opacity-10 text-warning">Due Soon</span>
                            <?php else: ?>
                              <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium bg-meta-3 bg-opacity-10 text-meta-3">Active</span>
                            <?php endif; ?>
                            <p class="text-xs text-body mt-0.5"><?= date('M d, Y', $dueDate) ?> &middot; <?= $asgn['submission_count'] ?> submitted</p>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

            </div>
            <!-- ===== Two-column layout End ===== -->

            <!-- ===== Subjects Overview ===== -->
            <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark mb-6">
              <div class="border-b border-stroke px-6 py-4 dark:border-strokedark flex items-center justify-between">
                <h3 class="font-semibold text-black dark:text-white">Subjects Overview</h3>
                <a href="course.php" class="text-xs text-primary hover:underline">Manage subjects</a>
              </div>
              <div class="px-5 pb-5 pt-3">
                <div class="flex flex-col">
                  <div class="grid grid-cols-3 rounded-sm bg-gray-2 dark:bg-meta-4 sm:grid-cols-4">
                    <div class="p-2.5 xl:p-4">
                      <h5 class="text-sm font-medium uppercase xsm:text-base">Subject</h5>
                    </div>
                    <div class="p-2.5 text-center xl:p-4">
                      <h5 class="text-sm font-medium uppercase xsm:text-base">Code</h5>
                    </div>
                    <div class="p-2.5 text-center xl:p-4">
                      <h5 class="text-sm font-medium uppercase xsm:text-base">Materials</h5>
                    </div>
                    <div class="hidden p-2.5 text-center sm:block xl:p-4">
                      <h5 class="text-sm font-medium uppercase xsm:text-base">Assignments</h5>
                    </div>
                  </div>

                  <?php if (empty($subjectsWithCounts)): ?>
                    <div class="py-6 text-center text-sm text-body">No subjects found. <a href="course.php" class="text-primary hover:underline">Add one now.</a></div>
                  <?php endif; ?>

                  <?php foreach ($subjectsWithCounts as $i => $subject): ?>
                    <div class="grid grid-cols-3 <?= $i < count($subjectsWithCounts) - 1 ? 'border-b border-stroke dark:border-strokedark' : '' ?> sm:grid-cols-4">
                      <div class="flex items-center gap-2 p-2.5 xl:p-4">
                        <p class="font-medium text-black dark:text-white"><?= htmlspecialchars($subject['name']) ?></p>
                      </div>
                      <div class="flex items-center justify-center p-2.5 xl:p-4">
                        <p class="text-sm font-medium text-body"><?= htmlspecialchars($subject['code']) ?></p>
                      </div>
                      <div class="flex items-center justify-center p-2.5 xl:p-4">
                        <p class="font-medium text-black dark:text-white"><?= $subject['material_count'] ?></p>
                      </div>
                      <div class="hidden items-center justify-center p-2.5 sm:flex xl:p-4">
                        <p class="font-medium text-black dark:text-white"><?= $subject['assignment_count'] ?></p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
            <!-- ===== Subjects Overview End ===== -->

            <!-- ===== Recent Students ===== -->
            <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
              <div class="border-b border-stroke px-6 py-4 dark:border-strokedark">
                <h3 class="font-semibold text-black dark:text-white">Recently Registered Students</h3>
              </div>
              <div class="px-5 pb-5 pt-3">
                <div class="flex flex-col">
                  <div class="grid grid-cols-3 rounded-sm bg-gray-2 dark:bg-meta-4 sm:grid-cols-4">
                    <div class="p-2.5 xl:p-4">
                      <h5 class="text-sm font-medium uppercase xsm:text-base">Name</h5>
                    </div>
                    <div class="hidden p-2.5 sm:block xl:p-4">
                      <h5 class="text-sm font-medium uppercase xsm:text-base">Email</h5>
                    </div>
                    <div class="p-2.5 text-center xl:p-4">
                      <h5 class="text-sm font-medium uppercase xsm:text-base">Roll No.</h5>
                    </div>
                    <div class="p-2.5 text-center xl:p-4">
                      <h5 class="text-sm font-medium uppercase xsm:text-base">Joined</h5>
                    </div>
                  </div>

                  <?php if (empty($recentStudents)): ?>
                    <div class="py-6 text-center text-sm text-body">No students registered yet.</div>
                  <?php endif; ?>

                  <?php foreach ($recentStudents as $i => $student): ?>
                    <div class="grid grid-cols-3 <?= $i < count($recentStudents) - 1 ? 'border-b border-stroke dark:border-strokedark' : '' ?> sm:grid-cols-4">
                      <div class="flex items-center p-2.5 xl:p-4">
                        <p class="font-medium text-black dark:text-white"><?= htmlspecialchars($student['name']) ?></p>
                      </div>
                      <div class="hidden items-center p-2.5 sm:flex xl:p-4">
                        <p class="text-sm text-body"><?= htmlspecialchars($student['email']) ?></p>
                      </div>
                      <div class="flex items-center justify-center p-2.5 xl:p-4">
                        <p class="font-medium text-black dark:text-white"><?= htmlspecialchars($student['roll_no'] ?? 'N/A') ?></p>
                      </div>
                      <div class="flex items-center justify-center p-2.5 xl:p-4">
                        <p class="text-sm text-body"><?= date('M d, Y', strtotime($student['created_at'])) ?></p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
            <!-- ===== Recent Students End ===== -->

          </div>
        </main>
        <!-- ===== Main Content End ===== -->
      </div>
      <!-- ===== Content Area End ===== -->
    </div>
    <!-- ===== Page Wrapper End ===== -->
  <script defer src="bundle.js"></script></body>
</html>
