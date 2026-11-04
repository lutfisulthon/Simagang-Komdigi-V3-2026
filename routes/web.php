<?php

use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Admin\AdminSanitizeAttendancePhotosController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminInternController;
use App\Http\Controllers\Admin\AdminMentorController;
use App\Http\Controllers\Admin\AdminMitraController;
use App\Http\Controllers\Admin\AdminMonitoringController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminLogbookController;
use App\Http\Controllers\Admin\AdminLowonganController;
use App\Http\Controllers\Admin\AdminVerifikasiLowonganController;
use App\Http\Controllers\Admin\AdminMonitoringIndustriController;
use App\Http\Controllers\Admin\AdminRagController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\InstitutionController;
use App\Http\Controllers\Intern\MicroSkillController as InternMicroSkillController;
use App\Http\Controllers\Mentor\MicroSkillController as MentorMicroSkillController;
use App\Http\Controllers\Admin\AdminMicroSkillController;
use App\Http\Controllers\Admin\AdminMicroSkillLeaderboardController;
use App\Http\Controllers\Mentor\DashboardController as MentorDashboardController;
use App\Http\Controllers\Mentor\InternController as MentorInternController;
use App\Http\Controllers\Mentor\AttendanceController as MentorAttendanceController;
use App\Http\Controllers\Mentor\LogbookController as MentorLogbookController;
use App\Http\Controllers\Mentor\ReportController as MentorReportController;
use App\Http\Controllers\Mentor\MicroSkillLeaderboardController as MentorMicroSkillLeaderboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Intern\AttendanceController;
use App\Http\Controllers\Intern\DashboardController;
use App\Http\Controllers\Intern\LogbookController;
use App\Http\Controllers\Intern\ReportController;
use App\Http\Controllers\Intern\MicroSkillLeaderboardController as InternMicroSkillLeaderboardController;
use App\Http\Controllers\Intern\ProfileController;
use App\Http\Controllers\Admin\AdminSharingSessionController;
use App\Http\Controllers\Admin\ActivityPostController;
use App\Http\Controllers\Intern\SharingSessionController;
use App\Http\Controllers\Mentor\ProfileController as MentorProfileController;
use App\Http\Controllers\Mentor\CertificateController;
use App\Http\Controllers\Admin\AdminCertificateController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Institusi\DaftarInstitusiController;
use App\Http\Controllers\Institusi\DashboardController as InstitusiDashboardController;
use App\Http\Controllers\Institusi\PengajuanController;
use App\Http\Controllers\Institusi\LowonganController;
use App\Http\Controllers\Admin\AdminPengajuanMagang;
use App\Http\Controllers\Institusi\AttendanceController as InstitusiAttendanceController;
use App\Http\Controllers\Institusi\InternController as InstitusiInternController;
use App\Http\Controllers\Institusi\LogbookController as InstitusiLogbookController;
use App\Http\Controllers\Institusi\MicroSkillController as InstitusiMicroSkillController;
use App\Http\Controllers\Institusi\ProfileController as InstitusiProfileController;
use App\Http\Controllers\Institusi\CertificateController as InstitusiCertificateController;
use App\Http\Controllers\Industri\DaftarAkunController;
use App\Http\Controllers\Industri\IndustriDashboardController as IndustriDashboardController;
use App\Http\Controllers\Industri\IndustriProfileController as IndustriProfileController;
use App\Http\Controllers\Industri\IndustriLowonganController;
use App\Http\Controllers\Industri\IndustriPengajuanController;
use App\Http\Controllers\Industri\IndustriInternController;
use App\Http\Controllers\Industri\IndustriAttendanceController;
use App\Http\Controllers\Industri\IndustriLogbookController;
use App\Http\Controllers\Industri\IndustriMicroSkillController;
use App\Http\Controllers\Industri\IndustriReportController;
use App\Http\Controllers\PengajuanFileController;
use App\Http\Controllers\SecureDownloadController;
use App\Models\Testimonial;
use App\Models\ActivityPost;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    $partnerFiles = Storage::disk('public')->files('partners');
    $partners = collect($partnerFiles)
        ->filter(fn ($path) => preg_match('/\.(png|jpe?g|gif|svg|webp)$/i', $path))
        ->map(fn ($path) => asset('storage/' . str_replace('%2F', '/', rawurlencode($path))))
        ->values();

    $testimonials = Testimonial::with(['intern', 'finalReport'])->orderBy('created_at', 'desc')->limit(3)->get();
    $totalPesertaAktif = \App\Models\Intern::count();

    if ($totalPesertaAktif > 10) {
        $totalPesertaAktif = ($totalPesertaAktif -  10) . '+';
    } 

    $lowongans = \App\Models\Lowongan::with('industri')
        ->where('status_verifikasi', 'disetujui')
        ->where('status', 'dibuka')
        ->latest()
        ->limit(6)
        ->get();
    
    $weekStart = \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::SATURDAY);
$weekEnd = $weekStart->copy()->addDays(6);

$weeklySharingSessions = \App\Models\SharingSession::with(['speakerUser', 'moderatorUser'])
    ->whereBetween('session_date', [
        $weekStart->toDateString(),
        $weekEnd->toDateString()
    ])
    ->orderBy('session_date', 'asc')
    ->orderBy('start_time', 'asc')
    ->take(4)
    ->get();

$today = \Carbon\Carbon::today();

$featuredSharingSession = $weeklySharingSessions
    ->first(function ($session) use ($today) {
        return $session->session_date->isSameDay($today);
    });

if (!$featuredSharingSession) {
    $featuredSharingSession = $weeklySharingSessions
        ->first(function ($session) use ($today) {
            return $session->session_date->greaterThan($today);
        });
}

if (!$featuredSharingSession) {
    $featuredSharingSession = $weeklySharingSessions->last();
}

$sideSharingSessions = $weeklySharingSessions
    ->filter(function ($session) use ($featuredSharingSession) {
        return !$featuredSharingSession || $session->id !== $featuredSharingSession->id;
    })
    ->values()
    ->take(3);
$landingView = \App\Models\PageView::firstOrCreate(
    ['page' => 'landingpage'],
    ['views' => 0]
);

if (!session()->has('landingpage_view_counted')) {
    $landingView->increment('views');
    session(['landingpage_view_counted' => true]);
    $landingView->refresh();
}

$visitorCount = $landingView->views;

$articleActivities = ActivityPost::published()
    ->where('type', 'artikel')
    ->orderByDesc('published_at')
    ->orderByDesc('created_at')
    ->take(3)
    ->get();

$youtubeActivities = ActivityPost::published()
    ->where('type', 'youtube')
    ->orderByDesc('published_at')
    ->orderByDesc('created_at')
    ->take(3)
    ->get();

$totalArticleActivities = ActivityPost::published()
    ->where('type', 'artikel')
    ->count();

$totalYoutubeActivities = ActivityPost::published()
    ->where('type', 'youtube')
    ->count();

    return view('landingpage', compact(
    'partners',
    'testimonials',
    'totalPesertaAktif',
    'lowongans',
    'weeklySharingSessions',
    'featuredSharingSession',
    'sideSharingSessions',
    'weekStart',
    'weekEnd',
    'visitorCount',
    'articleActivities',
    'youtubeActivities',
    'totalArticleActivities',
    'totalYoutubeActivities',
));

})->name('landing');
Route::get('/sharing-session/{sharingSession}', function (\App\Models\SharingSession $sharingSession) {
    $sharingSession->load(['speakerUser', 'moderatorUser']);

    $weekStart = $sharingSession->session_date->copy()->startOfWeek(\Carbon\Carbon::SATURDAY);
    $weekEnd = $weekStart->copy()->addDays(6);

    $otherSharingSessions = \App\Models\SharingSession::with(['speakerUser', 'moderatorUser'])
        ->whereBetween('session_date', [
            $weekStart->toDateString(),
            $weekEnd->toDateString()
        ])
        ->where('id', '!=', $sharingSession->id)
        ->orderBy('session_date', 'asc')
        ->orderBy('start_time', 'asc')
        ->take(3)
        ->get();

    return view('sharing-session-detail', compact(
        'sharingSession',
        'otherSharingSessions',
        'weekStart',
        'weekEnd'
    ));
})->name('public.sharing-session.show');

Route::get('/aktivitas', function (\Illuminate\Http\Request $request) {
    $type = $request->query('type', 'artikel');

    if (! in_array($type, ['artikel', 'youtube'])) {
        $type = 'artikel';
    }

    $posts = ActivityPost::published()
        ->where('type', $type)
        ->orderByDesc('published_at')
        ->orderByDesc('created_at')
        ->paginate(9)
        ->withQueryString();

    return view('activity-post-list', compact('posts', 'type'));
})->name('public.activity.index');

Route::get('/aktivitas/{activityPost:slug}', function (ActivityPost $activityPost) {
    abort_if(!$activityPost->is_published, 404);

    $relatedPosts = ActivityPost::published()
        ->where('id', '!=', $activityPost->id)
        ->where('type', $activityPost->type)
        ->orderByDesc('published_at')
        ->orderByDesc('created_at')
        ->take(3)
        ->get();

    return view('activity-post-detail', compact(
        'activityPost',
        'relatedPosts'
    ));
})->name('public.activity.show');

Route::get('/daftar-lowongan', function () {

    $lowongans = \App\Models\Lowongan::with('industri')
        ->where('status_verifikasi', 'disetujui')
        ->where('status', 'dibuka')
        ->latest()
        ->paginate(10);

    $totalLowongan = \App\Models\Lowongan::count();

    $perusahaans = \App\Models\Industri::orderBy('nama_industri')->get();

    $divisis = \App\Models\Lowongan::select('divisi')
        ->distinct()
        ->whereNotNull('divisi')
        ->pluck('divisi');

    return view('daftarlowongan', compact('lowongans', 'totalLowongan', 'perusahaans', 'divisis'));

})->name('daftar_lowongan');



Route::get('/convert-font', function () {
    $fontPath = storage_path('app/fonts/Poppins-Reguler.ttf');

    TCPDF_FONTS::addTTFfont(
        $fontPath,
        'TrueTypeUnicode',
        '',
        32
    );

    return 'Poppins Reguler berhasil di-convert';
});

// Artikel
Route::get('/artikel1', function () {
    return view('artikel.artikel_1');
})->name('artikel_1');

Route::get('/artikel2', function () {
    return view('artikel.artikel_2');
})->name('artikel_2');

Route::get('/artikel3', function () {
    return view('artikel.artikel_3');
})->name('artikel_3');

// API Routes for Institution Search
Route::get('/api/institutions/search', [InstitutionController::class, 'searchUniversities'])->name('api.institutions.search');
Route::get('/api/institutions/all', [InstitutionController::class, 'getAllUniversities'])->name('api.institutions.all');

// API Route cek hari libur (real-time, butuh auth)
Route::middleware('auth')->get('/api/attendance/is-holiday', [HolidayController::class, 'check'])->name('api.attendance.is-holiday');

// Protected download route for pengajuan documents
Route::get('/pengajuan/{pengajuan}/surat', PengajuanFileController::class)
    ->middleware('auth')
    ->name('pengajuan.surat');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])
        ->name('password.request');

    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetPasswordForm'])
        ->name('password.reset');

    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
        ->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Daftar Institusi
// Route::get('/institusi/dashboard', [InstitusiDashboardController::class, 'index'])->name('institusi.dashboard');
Route::middleware(['auth', 'institusi'])->prefix('institusi')->name('institusi.')->group(function () {
    Route::get('/dashboard', [InstitusiDashboardController::class, 'index'])->name('dashboard');
    Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
    Route::get(
        '/pengajuan/create/{lowongan}',
        [PengajuanController::class, 'create']
    )->name('pengajuan.create');
    Route::post('/pengajuan', [PengajuanController::class, 'store'])->name('pengajuan.store');
    Route::get('/pengajuan/{id}', [PengajuanController::class, 'show'])->name('pengajuan.show');
    Route::get('/pengajuan/{id}/edit', [PengajuanController::class, 'edit'])->name('pengajuan.edit');
    Route::put('/pengajuan/{id}', [PengajuanController::class, 'update'])->name('pengajuan.update');
    Route::delete('/pengajuan/{id}', [PengajuanController::class, 'destroy'])->name('pengajuan.destroy');
    // surat balasan untuk institusi
    Route::get('/surat-balasan/{pengajuan}', [PengajuanController::class, 'generateSuratBalasan'])->name('pengajuan.surat-balasan');
    // Profile routes for institusi
    Route::get('/profile', [InstitusiProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [InstitusiProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [InstitusiProfileController::class, 'update'])->name('profile.update');
    // Attendance monitoring for institusi
    Route::get('/attendance', [InstitusiAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/detail/{intern}', [InstitusiAttendanceController::class, 'show'])
        ->name('attendance.show');
    Route::get('/attendance/photo/{filename}', [InstitusiAttendanceController::class, 'servePhoto'])
        ->where('filename', '[^/\\\\]+')
        ->name('attendance.photo');
    // Intern management for institusi
    Route::get('/intern', [InstitusiInternController::class, 'index'])->name('intern.index');
    // Logbook monitoring for institusi
    Route::get('/logbook', [InstitusiLogbookController::class, 'index'])->name('logbook.index');
    Route::get('/logbook/photo/{filename}', [InstitusiLogbookController::class, 'servePhoto'])
        ->where('filename', '[^/\\\\]+')
        ->name('logbook.photo');
    Route::get('/logbook/{logbook}', [InstitusiLogbookController::class, 'show'])->name('logbook.show');
    // Certificate management for institusi
    Route::get('/sertifikat', [InstitusiCertificateController::class, 'index'])->name('certificate.index');
    Route::get('/sertifikat/{certificate}', [InstitusiCertificateController::class, 'show'])->name('certificate.show');
    // Mikro skill monitoring for institusi
    Route::get('/microskill', [InstitusiMicroSkillController::class, 'index'])->name('microskill.index');
    Route::get('/microskill/photo/{filename}', [InstitusiMicroSkillController::class, 'servePhoto'])
        ->where('filename', '[^/\\\\]+')
        ->name('microskill.photo');

    // Lowongan management for institusi
    Route::get('/lowongan', [LowonganController::class, 'index'])->name('lowongan.index');
    Route::get('/lowongan/{id}', [LowonganController::class, 'show'])->name('lowongan.show');
}); 
Route::resource('institusi', DaftarInstitusiController::class);

// Routes untuk pendaftaran akun industri
Route::get('/register-industri', [DaftarAkunController::class, 'create'])
    ->name('industri.create');

Route::post('/register-industri', [DaftarAkunController::class, 'store'])
    ->name('industri.store');

// Routes Role Industri
Route::middleware(['auth', 'industri'])->prefix('industri')->name('industri.')->group(function () {
    Route::get('/dashboard', [IndustriDashboardController::class, 'index'])->name('dashboard');     
    Route::get('/profile/create', [IndustriProfileController::class, 'create'])->name('profile.create');  
    Route::post('/profile', [IndustriProfileController::class, 'store'])->name('profile.store');  
    
    // Lowongan Routes
    Route::get('/lowongan', [IndustriLowonganController::class, 'index'])->name('lowongan.index');
    Route::get('/lowongan/create', [IndustriLowonganController::class, 'create'])->name('lowongan.create');
    Route::post('/lowongan', [IndustriLowonganController::class, 'store'])->name('lowongan.store');
    Route::get('/lowongan/{id}', [IndustriLowonganController::class, 'show'])->name('lowongan.show');
    Route::get('/lowongan/{id}/edit', [IndustriLowonganController::class, 'edit'])->name('lowongan.edit');
    Route::put('/lowongan/{id}', [IndustriLowonganController::class, 'update'])->name('lowongan.update');
    Route::delete('/lowongan/{id}', [IndustriLowonganController::class, 'destroy'])->name('lowongan.destroy');

    // Pengajuan Routes
    Route::get('/pengajuan', [IndustriPengajuanController::class, 'index'])->name('pengajuan.index');
    // Route::get('/pengajuan/create', [IndustriPengajuanController::class, 'create'])->name('pengajuan.create');
    // Route::post('/pengajuan', [IndustriPengajuanController::class, 'store'])->name('pengajuan.store');
    Route::get('/pengajuan/{id}', [IndustriPengajuanController::class, 'show'])->name('pengajuan.show');
    Route::put('/pengajuan/{pengajuan}/update-status', [IndustriPengajuanController::class, 'updateStatus'])
    ->name('pengajuan.update-status');
    Route::get('/pengajuan/surat-balasan/{pengajuan}', [IndustriPengajuanController::class, 'generateSuratBalasan'])->name('pengajuan.surat-balasan');

    // Kelola Intern Routes
    Route::get('/intern', [IndustriInternController::class, 'index'])->name('intern.index');
    Route::get('/intern/create', [IndustriInternController::class, 'create'])->name('intern.create');
    Route::post('/intern', [IndustriInternController::class, 'store'])->name('intern.store');
    Route::get('/intern/{intern}', [IndustriInternController::class, 'show'])->name('intern.show'); 
    Route::get('/intern/{intern}/edit', [IndustriInternController::class, 'edit'])->name('intern.edit');
    Route::put('/intern/{intern}', [IndustriInternController::class, 'update'])->name('intern.update');
    Route::delete('/intern/{intern}', [IndustriInternController::class, 'destroy'])->name('intern.destroy');

    // Attendance monitoring for institusi
    Route::get('/attendance', [IndustriAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/detail/{intern}', [IndustriAttendanceController::class, 'show'])
        ->name('attendance.show');
    Route::get('/attendance/{attendance}/detail', [IndustriAttendanceController::class, 'showDetail'])
        ->name('attendance.detail');
    Route::put('/attendance/{attendance}/document-status', [IndustriAttendanceController::class, 'updateDocumentStatus'])
        ->name('attendance.update-document-status');
    Route::get('/attendance/photo/{filename}', [IndustriAttendanceController::class, 'servePhoto'])
        ->where('filename', '[^/\\\\]+')
        ->name('attendance.photo');
    
    Route::get('/attendance/document/{filename}', [IndustriAttendanceController::class, 'serveDocument'])->name('attendance.document');

    // Logbook monitoring for institusi
    Route::get('/logbook', [IndustriLogbookController::class, 'index'])->name('logbook.index');
    Route::get('/logbook/photo/{filename}', [IndustriLogbookController::class, 'servePhoto'])
        ->where('filename', '[^/\\\\]+')
        ->name('logbook.photo');
    Route::get('/logbook/{logbook}', [IndustriLogbookController::class, 'show'])->name('logbook.show');

    // Mikro skill monitoring for institusi
    Route::get('/microskill', [IndustriMicroSkillController::class, 'index'])->name('microskill.index');
    Route::get('/microskill/photo/{filename}', [IndustriMicroSkillController::class, 'servePhoto'])
        ->where('filename', '[^/\\\\]+')
        ->name('microskill.photo');

    // Report Monitoring Routes
    Route::get('/report', [IndustriReportController::class, 'index'])->name('report.index');
    Route::get('/report/{report}', [IndustriReportController::class, 'show'])->name('report.show');
    Route::put('/report/{report}/status', [IndustriReportController::class, 'updateStatus'])->name('report.update-status');

    
});

// File Download Route with access validation per user
Route::get('/download/{path}', SecureDownloadController::class)
    ->middleware('auth')
    ->where('path', '.*')
    ->name('download');

// Intern Routes
Route::middleware(['auth', 'intern'])->prefix('intern')->name('intern.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
   // Sharing Session Routes
Route::get('/sharing-session', [SharingSessionController::class, 'index'])
    ->name('sharing-session.index');

Route::get('/sharing-session/{sharingSession}', [SharingSessionController::class, 'show'])
    ->name('sharing-session.show');

Route::get('/sharing-session/{sharingSession}/edit-materi', [SharingSessionController::class, 'editMateri'])
    ->name('sharing-session.edit-materi');

Route::put('/sharing-session/{sharingSession}/update-materi', [SharingSessionController::class, 'updateMateri'])
    ->name('sharing-session.update-materi');

    Route::get(
            'certificates/{certificate}/print',
            [CertificateController::class, 'print']
        )->name('certificates.print');
    
    // Attendance Routes
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
    Route::get('/attendance/photo/{filename}', [AttendanceController::class, 'servePhoto'])
        ->name('attendance.photo')
        ->where('filename', '[^/]+');
    Route::get('/attendance/document/{filename}', [AttendanceController::class, 'serveDocument'])->name('attendance.document');
    
    // Logbook Routes
    Route::get('/logbook', [LogbookController::class, 'index'])->name('logbook.index');
    Route::get('/logbook/create', [LogbookController::class, 'create'])->name('logbook.create');
    Route::post('/logbook', [LogbookController::class, 'store'])->name('logbook.store');
    Route::get('/logbook/{logbook}/edit', [LogbookController::class, 'edit'])->name('logbook.edit');
    Route::put('/logbook/{logbook}', [LogbookController::class, 'update'])->name('logbook.update');
    Route::delete('/logbook/{logbook}', [LogbookController::class, 'destroy'])->name('logbook.destroy');
    Route::get('/logbook/photo/{filename}', [LogbookController::class, 'servePhoto'])->name('logbook.photo');
    Route::get('/logbook/{logbook}', [LogbookController::class, 'show'])->name('logbook.show');

    // Micro Skill Routes
    Route::get('/microskill', [InternMicroSkillController::class, 'index'])->name('microskill.index');
    Route::get('/microskill/create', [InternMicroSkillController::class, 'create'])->name('microskill.create');
    Route::post('/microskill', [InternMicroSkillController::class, 'store'])->name('microskill.store');
    Route::get('/microskill/{submission}/edit', [InternMicroSkillController::class, 'edit'])->name('microskill.edit');
    Route::put('/microskill/{submission}', [InternMicroSkillController::class, 'update'])->name('microskill.update');
    Route::delete('/microskill/{submission}', [InternMicroSkillController::class, 'destroy'])->name('microskill.destroy');
    Route::get('/microskill/leaderboard', [InternMicroSkillLeaderboardController::class, 'index'])->name('microskill.leaderboard');
    Route::get('/microskill/photo/{filename}', [InternMicroSkillController::class, 'servePhoto'])->name('microskill.photo');
    
    // Final Report Routes
    Route::get('/report', [ReportController::class, 'index'])->name('report.index');
    Route::post('/report', [ReportController::class, 'store'])->name('report.store');
    Route::put('/report/{report}', [ReportController::class, 'update'])->name('report.update');
    Route::post('/report/{report}/testimonial', [ReportController::class, 'storeTestimonial'])->name('report.storeTestimonial');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource(
    'sharing-session',
    AdminSharingSessionController::class
    );

    Route::resource('aktivitas', ActivityPostController::class)
    ->parameters(['aktivitas' => 'aktivita']);

    Route::middleware('role:super_admin')->group(function () {
        Route::get('/accounts', [AdminAccountController::class, 'index'])->name('accounts.index');
        Route::get('/accounts/create', [AdminAccountController::class, 'create'])->name('accounts.create');
        Route::post('/accounts', [AdminAccountController::class, 'store'])->name('accounts.store');
        Route::get('/lowongan/{id}/edit', [AdminLowonganController::class, 'edit'])->name('lowongan.edit');
        Route::put('/lowongan/{id}', [AdminLowonganController::class, 'update'])->name('lowongan.update');
        Route::get('/accounts/{user}/edit', [AdminAccountController::class, 'edit'])->name('accounts.edit');
        Route::put('/accounts/{user}', [AdminAccountController::class, 'update'])->name('accounts.update');
        Route::delete('/accounts/{user}', [AdminAccountController::class, 'destroy'])->name('accounts.destroy');
        
        // RAG Knowledge Management Routes
        Route::get('/rag-knowledge', [AdminRagController::class, 'index'])->name('rag.index');
        Route::post('/rag-knowledge', [AdminRagController::class, 'store'])->name('rag.store');
        Route::get('/rag-knowledge/{folder}/{filename}', [AdminRagController::class, 'show'])->name('rag.show');
        Route::put('/rag-knowledge/{folder}/{filename}', [AdminRagController::class, 'update'])->name('rag.update');
        Route::delete('/rag-knowledge/{folder}/{filename}', [AdminRagController::class, 'destroy'])->name('rag.destroy');
        Route::post('/rag-knowledge/sync', [AdminRagController::class, 'sync'])->name('rag.sync');
        Route::post('/rag-knowledge/clear-cache', [AdminRagController::class, 'clearCache'])->name('rag.clearCache');
    });

    // Mentor Management Routes
    Route::get('/mentor', [AdminMentorController::class, 'index'])->name('mentor.index');
    Route::get('/mentor/create', [AdminMentorController::class, 'create'])->name('mentor.create');
    Route::post('/mentor', [AdminMentorController::class, 'store'])->name('mentor.store');
    Route::get('/mentor/{mentor}/edit', [AdminMentorController::class, 'edit'])->name('mentor.edit');
    Route::put('/mentor/{mentor}', [AdminMentorController::class, 'update'])->name('mentor.update');
    Route::delete('/mentor/{mentor}', [AdminMentorController::class, 'destroy'])->name('mentor.destroy');

    // Intern Management Routes
    Route::get('/intern', [AdminInternController::class, 'index'])->name('intern.index');
    Route::get('/intern/create', [AdminInternController::class, 'create'])->name('intern.create');
    Route::post('/intern', [AdminInternController::class, 'store'])->name('intern.store');
    Route::get('/intern/{intern}', [AdminInternController::class, 'show'])->name('intern.show');
    Route::get('/intern/{intern}/edit', [AdminInternController::class, 'edit'])->name('intern.edit');
    Route::put('/intern/{intern}', [AdminInternController::class, 'update'])->name('intern.update');
    Route::delete('/intern/{intern}', [AdminInternController::class, 'destroy'])->name('intern.destroy');

    Route::get('/mitra', [AdminMitraController::class, 'index'])->name('mitra.index');
Route::get('/mitra/create', [AdminMitraController::class, 'create'])->name('mitra.create');
Route::post('/mitra', [AdminMitraController::class, 'store'])->name('mitra.store');

Route::get('/mitra/{jenis}/{id}/edit', [AdminMitraController::class, 'edit'])->name('mitra.edit');
Route::put('/mitra/{jenis}/{id}', [AdminMitraController::class, 'update'])->name('mitra.update');
Route::delete('/mitra/{jenis}/{id}', [AdminMitraController::class, 'destroy'])->name('mitra.destroy');

    // Attendance Monitoring Route
    Route::get('/attendance/photo/{filename}', [AdminAttendanceController::class, 'servePhoto'])
    ->name('attendance.photo')
    ->where('filename', '[^/]+');
    Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/sanitize-photos', AdminSanitizeAttendancePhotosController::class)
        ->name('attendance.sanitize-photos');
    Route::get('/attendance/{attendance}', [AdminAttendanceController::class, 'show'])->name('attendance.show');
    Route::get('/attendance/document/{filename}', [AdminAttendanceController::class, 'serveDocument'])->name('attendance.document');
    Route::put('/attendance/{attendance}/document-status', [AdminAttendanceController::class, 'updateDocumentStatus'])->name('attendance.update-document-status');
    // Logbook Monitoring Routes
    Route::get('/logbook/photo/{filename}', [AdminLogbookController::class, 'servePhoto'])
        ->name('logbook.photo')
        ->middleware('signed')
        ->where('filename', '[^/]+');
    Route::get('/logbook', [AdminLogbookController::class, 'index'])->name('logbook.index');
    Route::delete('/logbook/{logbook}', [AdminLogbookController::class, 'destroy'])->name('logbook.destroy');

    // Report Monitoring Routes
    Route::get('/report', [AdminReportController::class, 'index'])->name('report.index');
    Route::get('/report/{report}', [AdminReportController::class, 'show'])->name('report.show');
    Route::put('/report/{report}/status', [AdminReportController::class, 'updateStatus'])->name('report.update-status');

    // Micro Skill Routes
    Route::get('/microskill/photo/{filename}', [AdminMicroSkillController::class, 'servePhoto'])
        ->name('microskill.photo')
        ->middleware('signed')
        ->where('filename', '[^/]+');
    Route::get('/microskill/create', [AdminMicroSkillController::class, 'create'])->name('microskill.create');
    Route::post('/microskill', [AdminMicroSkillController::class, 'store'])->name('microskill.store');
    Route::delete('/microskill/{id}', [AdminMicroSkillController::class, 'destroy'])->name('microskill.destroy');
    Route::get('/microskill/{id}/detail', [AdminMicroSkillController::class, 'show'])->name('microskill.show');
    Route::get('/microskill', [AdminMicroSkillController::class, 'index'])->name('microskill.index');
    Route::get('/microskill/leaderboard', [AdminMicroSkillLeaderboardController::class, 'index'])->name('microskill.leaderboard');

    // Monitoring Routes
    Route::get('/monitoring', [AdminMonitoringController::class, 'index'])->name('monitoring.index');
    Route::post('/monitoring/{intern}/mark-released', [AdminMonitoringController::class, 'markAsReleased'])->name('monitoring.mark-released');
    Route::post('/monitoring/{intern}/mark-active', [AdminMonitoringController::class, 'markAsActive'])->name('monitoring.mark-active');
    Route::get('/monitoring/export', [AdminMonitoringController::class, 'export'])->name('monitoring.export');

    // Monitoring Industri (Admin)
    Route::get('monitoring/industri', [AdminMonitoringIndustriController::class, 'index'])->name('monitoring.industri.index');
    Route::get('monitoring/industri/{industri}', [AdminMonitoringIndustriController::class, 'show'])->name('monitoring.industri.show');

    // Certificate Management Routes
    Route::resource('certificates', AdminCertificateController::class)
            ->only(['index', 'create', 'store', 'show', 'update']);

    Route::get(
        'certificates/{certificate}/print',
        [AdminCertificateController::class, 'print']
    )->name('certificates.print');

    // Pengajuan Management Routes
    Route::get('/pengajuan', [AdminPengajuanMagang::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/{id}', [AdminPengajuanMagang::class, 'show'])->name('pengajuan.show');
    Route::put('/pengajuan/{pengajuan}/update-status', [AdminPengajuanMagang::class, 'updateStatus'])
    ->name('pengajuan.update-status');
    Route::delete('/pengajuan/{id}', [AdminPengajuanMagang::class, 'destroy'])->name('pengajuan.destroy');
    Route::get('/pengajuan/surat-balasan/{pengajuan}', [AdminPengajuanMagang::class, 'generateSuratBalasan'])->name('pengajuan.surat-balasan');

    // Team Management Routes
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::get('/team/create', [TeamController::class, 'create'])->name('team.create');
    Route::post('/team', [TeamController::class, 'store'])->name('team.store');
    Route::get('/team/{team}/edit', [TeamController::class, 'edit'])->name('team.edit');
    Route::put('/team/{team}', [TeamController::class, 'update'])->name('team.update');
    Route::delete('/team/{team}', [TeamController::class, 'destroy'])->name('team.destroy');  

    // Lowongan Management Routes
    Route::get('/lowongan', [AdminLowonganController::class, 'index'])->name('lowongan.index');
    Route::get('/lowongan/create', [AdminLowonganController::class, 'create'])->name('lowongan.create');
    Route::post('/lowongan', [AdminLowonganController::class, 'store'])->name('lowongan.store');
    Route::get('/lowongan/{id}', [AdminLowonganController::class, 'show'])->name('lowongan.show');
    Route::get('/lowongan/{id}/edit', [AdminLowonganController::class, 'edit'])->name('lowongan.edit');
    Route::put('/lowongan/{id}', [AdminLowonganController::class, 'update'])->name('lowongan.update');
    Route::delete('/lowongan/{id}', [AdminLowonganController::class, 'destroy'])->name('lowongan.destroy');
    Route::patch('/lowongan/{id}/approve', [AdminLowonganController::class, 'approve'])->name('lowongan.approve');
    Route::patch('/lowongan/{id}/reject', [AdminLowonganController::class, 'reject'])->name('lowongan.reject');

    //Verifikasi Lowongan Routes
    Route::get('/verifikasi-lowongan', [AdminVerifikasiLowonganController::class, 'index'])->name('verifikasi.index');
    Route::get('/verifikasi-lowongan/{id}', [AdminVerifikasiLowonganController::class, 'show'])->name('verifikasi.show');
    Route::patch('/verifikasi-lowongan/{id}/approve', [AdminVerifikasiLowonganController::class, 'approve'])->name('verifikasi.approve');
    Route::patch('/verifikasi-lowongan/{id}/reject', [AdminVerifikasiLowonganController::class, 'reject'])->name('verifikasi.reject'); 

});

// Mentor Routes
Route::middleware(['auth', 'mentor'])->prefix('mentor')->name('mentor.')->group(function () {
    Route::get('/dashboard', [MentorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/interns', [MentorInternController::class, 'index'])->name('intern.index');
    Route::get('/interns/{intern}', [MentorInternController::class, 'show'])->name('intern.show');
    Route::get('/attendance', [MentorAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/photo/{filename}', [MentorAttendanceController::class, 'servePhoto'])
        ->name('attendance.photo')
        ->where('filename', '[^/]+');
    Route::get('/logbook', [MentorLogbookController::class, 'index'])->name('logbook.index');
    Route::get('/logbook/{logbook}', [MentorLogbookController::class, 'show'])->name('logbook.show');
    Route::post('/logbook/{logbook}/comment', [MentorLogbookController::class, 'comment'])->name('logbook.comment');
    Route::put('/logbook/{logbook}/approve', [MentorLogbookController::class, 'approve'])->name('logbook.approve');
    Route::get('/logbook/photo/{filename}', [MentorLogbookController::class, 'servePhoto'])
        ->name('logbook.photo')
        ->middleware('signed')
        ->where('filename', '[^/]+');
        Route::get('/report', [MentorReportController::class, 'index'])->name('report.index');
        Route::get('/report/{report}', [MentorReportController::class, 'show'])->name('report.show');
        Route::put('/report/{report}/grade', [MentorReportController::class, 'grade'])->name('report.grade');
        Route::get('/microskill', [MentorMicroSkillController::class, 'index'])->name('microskill.index');
        Route::get('/microskill/photo/{filename}', [MentorMicroSkillController::class, 'servePhoto'])
        ->name('microskill.photo')
        ->middleware('signed')
        ->where('filename', '[^/]+');
        Route::get('/microskill/{id}/detail', [MentorMicroSkillController::class, 'show'])->name('microskill.show');
    Route::get('/microskill/leaderboard', [MentorMicroSkillLeaderboardController::class, 'index'])->name('microskill.leaderboard');
    Route::resource('certificates', CertificateController::class)
            ->only(['index', 'create', 'store', 'show', "update"]);
    Route::get(
            'certificates/{certificate}/print',
            [CertificateController::class, 'print']
        )->name('certificates.print');
    
    // Profile Routes
    Route::get('/profile', [MentorProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [MentorProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [MentorProfileController::class, 'update'])->name('profile.update');
});

Route::post('/chatbot', [\App\Http\Controllers\ChatbotController::class, 'ask'])->name('chatbot.ask');

require __DIR__.'/workspace.php';
