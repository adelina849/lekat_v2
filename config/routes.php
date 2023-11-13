<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
//$route['default_controller'] = 'C_admin_login';
//$route['default_controller'] = 'C_kec_login/index';
$route['default_controller'] = 'Welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

//LOGIN
	$route['admin-cek-login'] = "C_admin_login/cek_login";
	$route['admin-cek-login/(:any)'] = 'C_admin_login/cek_login';

	$route['admin-login'] = "C_admin_login/index";
	$route['admin-login/(:any)'] = 'C_admin_login/index';

	$route['admin'] = "C_admin/index";
	$route['admin/(:any)'] = 'C_admin/index';
	
	$route['admin-logout'] = "C_admin_login/logout";
	$route['admin-logout/(:any)'] = 'C_admin_login/logout';
//LOGIN

//JABATAN KARYAWAN
	$route['admin-jabatan-simpan'] = "C_admin_jabatan/simpan";
	$route['admin-jabatan-simpan/(:any)'] = 'C_admin_jabatan/simpan';

	$route['admin-jabatan'] = "C_admin_jabatan/index";
	$route['admin-jabatan/(:any)'] = 'C_admin_jabatan/index/$1';

	$route['admin-jabatan-hapus'] = "C_admin_jabatan/hapus";
	$route['admin-jabatan-hapus/(:any)'] = 'C_admin_jabatan/hapus/$1';
//JABATAN KARYAWAN

//KARYAWAN
	$route['admin-karyawan'] = "C_admin_karyawan/index";
	$route['admin-karyawan/(:any)'] = 'C_admin_karyawan/index';

	$route['admin-karyawan-simpan'] = "C_admin_karyawan/simpan";
	$route['admin-karyawan-simpan/(:any)'] = 'C_admin_karyawan/simpan';

	$route['admin-karyawan-hapus'] = "C_admin_karyawan/hapus";
	$route['admin-karyawan-hapus/(:any)'] = 'C_admin_karyawan/hapus';
//KARYAWAN

//AKUN
	$route['admin-akun'] = "C_admin_akun/index";
	$route['admin-akun/(:any)'] = 'C_admin_akun/index';

	$route['admin-simpan'] = "C_admin_akun/simpan";
	$route['admin-simpan/(:any)'] = 'C_admin_akun/simpan';

	$route['admin-akun-hapus'] = "C_admin_akun/hapus";
	$route['admin-akun-hapus/(:any)'] = 'C_admin_akun/hapus';
//AKUN

//KECAMATAN
	$route['kecamatan'] = "C_admin_kecamatan/index";
	$route['kecamatan/(:any)'] = 'C_admin_kecamatan/index';

	$route['kecamatan-simpan'] = "C_admin_kecamatan/simpan";
	$route['kecamatan-simpan/(:any)'] = 'C_admin_kecamatan/simpan';

	$route['kecamatan-hapus'] = "C_admin_kecamatan/hapus";
	$route['kecamatan-hapus/(:any)'] = 'C_admin_kecamatan/hapus';
//KECAMATAN


//DESA
	$route['kecamatan-list-desa'] = "C_admin_desa/index_list_desa";
	$route['kecamatan-list-desa/(:any)'] = 'C_admin_desa/index_list_desa';

	$route['kecamatan-desa'] = "C_admin_desa/index";
	$route['kecamatan-desa/(:any)'] = 'C_admin_desa/index';
	$route['kecamatan-desa/(:any)/(:any)'] = 'C_admin_desa/index';

	$route['kecamatan-desa-simpan'] = "C_admin_desa/simpan";
	$route['kecamatan-desa-simpan/(:any)'] = 'C_admin_desa/simpan';

	$route['kecamatan-desa-hapus'] = "C_admin_desa/hapus";
	$route['kecamatan-desa-hapus/(:any)'] = 'C_admin_desa/hapus';
	$route['kecamatan-desa-hapus/(:any)/(:any)'] = 'C_admin_desa/hapus';
//DESA

//KATEGORI - LAPORAN
	$route['kategori-laporan'] = "C_admin_klaporan/index";
	$route['kategori-laporan/(:any)'] = 'C_admin_klaporan/index';

	$route['kategori-laporan-simpan'] = "C_admin_klaporan/simpan";
	$route['kategori-laporan-simpan/(:any)'] = 'C_admin_klaporan/simpan';

	$route['kategori-laporan-hapus'] = "C_admin_klaporan/hapus";
	$route['kategori-laporan-hapus/(:any)'] = 'C_admin_klaporan/hapus';
//KATEGORI - LAPORAN

//PERIODE - LAPORAN
	$route['periode-laporan'] = "C_admin_periode/index";
	$route['periode-laporan/(:any)'] = 'C_admin_periode/index';

	$route['periode-laporan-simpan'] = "C_admin_periode/simpan";
	$route['periode-laporan-simpan/(:any)'] = 'C_admin_periode/simpan';

	$route['periode-laporan-hapus'] = "C_admin_periode/hapus";
	$route['periode-laporan-hapus/(:any)'] = 'C_admin_periode/hapus';
//PERIODE - LAPORAN

//LAPORAN
	$route['board-laporan'] = "C_admin_board_laporan/index";
	$route['board-laporan/(:any)'] = 'C_admin_board_laporan/index';

	$route['jenis-laporan'] = "C_admin_laporan/index";
	$route['jenis-laporan/(:any)'] = 'C_admin_laporan/index';
	$route['jenis-laporan/(:any)/(:any)'] = 'C_admin_laporan/index';

	$route['jenis-laporan-simpan'] = "C_admin_laporan/simpan";
	$route['jenis-laporan-simpan/(:any)'] = 'C_admin_laporan/simpan';

	$route['jenis-laporan-hapus'] = "C_admin_laporan/hapus";
	$route['jenis-laporan-hapus/(:any)'] = 'C_admin_laporan/hapus';
	$route['jenis-laporan-hapus/(:any)/(:any)'] = 'C_admin_laporan/hapus';
	
	$route['laporan-kecamatan'] = "C_admin_hasil_laporan/index";
	$route['laporan-kecamatan/(:any)'] = 'C_admin_hasil_laporan/index';
	
	$route['laporan-kecamatan-perjenis'] = "C_admin_hasil_laporan/listHasilLaporan_perjenis";
	$route['laporan-kecamatan-perjenis/(:any)'] = 'C_admin_hasil_laporan/listHasilLaporan_perjenis';
	
	$route['laporan-kecamatan-perjenis-detail'] = "C_admin_hasil_laporan/listHasilLaporan_perjenis_detail";
	$route['laporan-kecamatan-perjenis-detail/(:any)'] = 'C_admin_hasil_laporan/listHasilLaporan_perjenis_detail';
	
	$route['detail-persetase-laporan-kecamatan'] = "C_admin_persen_laporan/index";
	$route['detail-persetase-laporan-kecamatan/(:any)'] = 'C_admin_persen_laporan/index';
	
	$route['detail-arsip-persetase-laporan-kecamatan'] = "C_admin_persen_laporan/arsip_laporan";
	$route['detail-arsip-persetase-laporan-kecamatan/(:any)'] = 'C_admin_persen_laporan/arsip_laporan';
	
	$route['akumulasi-laporan-kecamatan'] = "C_admin_akum_laporan/index";
	$route['akumulasi-laporan-kecamatan/(:any)'] = 'C_admin_akum_laporan/index';
	
	$route['akumulasi-laporan-kecamatan-periode'] = "C_admin_akum_laporan/akumulasi_periode";
	$route['akumulasi-laporan-kecamatan-periode/(:any)'] = 'C_admin_akum_laporan/akumulasi_periode';
	
	$route['akumulasi-laporan-kecamatan-periode-proses'] = "C_admin_akum_laporan/proses_laporan";
	$route['akumulasi-laporan-kecamatan-periode-proses/(:any)'] = 'C_admin_akum_laporan/proses_laporan';
	
	
	$route['laporan-per-periode'] = "C_admin_lap_periode/index";
	$route['laporan-per-periode/(:any)'] = 'C_admin_lap_periode/index';
	
	$route['detail-presentasi'] = "C_admin_lap_periode/detail";
	$route['detail-presentasi/(:any)'] = 'C_admin_lap_periode/detail';
	
	$route['akumulasi-laporan-kecamatan-cetak'] = "C_admin_akum_cetak_laporan/index";
	$route['akumulasi-laporan-kecamatan-cetak/(:any)'] = 'C_admin_akum_cetak_laporan/index';
	
	$route['akumulasi-laporan-kecamatan-periode-cetak'] = "C_admin_akum_cetak_laporan/akumulasi_periode";
	$route['akumulasi-laporan-kecamatan-periode-cetak/(:any)'] = 'C_admin_akum_cetak_laporan/akumulasi_periode';
	
	$route['akumulasi-laporan-kecamatan-periode-proses-cetak'] = "C_admin_akum_cetak_laporan/proses_laporan";
	$route['akumulasi-laporan-kecamatan-periode-proses-cetak/(:any)'] = 'C_admin_akum_cetak_laporan/proses_laporan';
//LAPORAN

//DESA
	$route['item-jenis-laporan'] = "C_admin_item_laporan/index";
	$route['item-jenis-laporan/(:any)'] = 'C_admin_item_laporan/index';
	$route['item-jenis-laporan/(:any)/(:any)'] = 'C_admin_item_laporan/index';

	$route['item-jenis-laporan-simpan'] = "C_admin_item_laporan/simpan";
	$route['item-jenis-laporan-simpan/(:any)'] = 'C_admin_item_laporan/simpan';

	$route['item-jenis-laporan-hapus'] = "C_admin_item_laporan/hapus";
	$route['item-jenis-laporan-hapus/(:any)'] = 'C_admin_item_laporan/hapus';
	$route['item-jenis-laporan-hapus/(:any)/(:any)'] = 'C_admin_item_laporan/hapus';
//DESA




//=====================================KECAMATAN======================================================
//LOGIN KECAMATAN
	$route['kecamatan-login'] = "C_kec_login/index";
	$route['kecamatan-login/(:any)'] = 'C_kec_login/index';
	
	$route['kecamatan-login-cek'] = "C_kec_login/cek_login";
	$route['kecamatan-login-cek/(:any)'] = 'C_kec_login/cek_login';
	
	$route['kecamatan-admin-dashboard'] = "C_kec/index";
	$route['kecamatan-admin-dashboard/(:any)'] = 'C_kec/index';
//LOGIN KECAMATAN

//KECAMATAN LIST LAPORAN
	$route['kecamatan-buat-laporan-dashboard'] = "C_kec/tampilkan_jenis_laporan";
	$route['kecamatan-buat-laporan-dashboard/(:any)'] = 'C_kec/tampilkan_jenis_laporan';
	

	$route['kecamatan-list-laporan'] = "C_kec_list_laporan/index";
	$route['kecamatan-list-laporan/(:any)'] = 'C_kec_list_laporan/index';
	$route['kecamatan-list-laporan/(:any)/(:any)'] = 'C_kec_list_laporan/index';

	$route['kecamatan-list-laporan-simpan'] = "C_kec_list_laporan/simpan";
	$route['kecamatan-list-laporan-simpan/(:any)'] = 'C_kec_list_laporan/simpan';

	$route['kecamatan-list-laporan-hapus'] = "C_kec_list_laporan/hapus";
	$route['kecamatan-list-laporan-hapus/(:any)'] = 'C_kec_list_laporan/hapus';
	$route['kecamatan-list-laporan-hapus/(:any)/(:any)'] = 'C_kec_list_laporan/hapus';
//KECAMATAN LIST LAPORAN

//BUAT LAPORAN KECAMATAN
	$route['buat-laporan'] = "C_kec_buat_laporan/index";
	$route['buat-laporan/(:any)'] = 'C_kec_buat_laporan/index';
	$route['buat-laporan/(:any)/(:any)'] = 'C_kec_buat_laporan/index';

	$route['buat-laporan-simpan'] = "C_kec_buat_laporan/simpan";
	$route['buat-laporan-simpan/(:any)'] = 'C_kec_buat_laporan/simpan';

	$route['buat-laporan-hapus'] = "C_kec_buat_laporan/hapus";
	$route['buat-laporan-hapus/(:any)'] = 'C_kec_buat_laporan/hapus';
	$route['buat-laporan-hapus/(:any)/(:any)'] = 'C_kec_buat_laporan/hapus';
	
	$route['buat-laporan-gambar'] = "C_kec_buat_laporan/view_tambah_gambar";
	$route['buat-laporan-gambar/(:any)'] = 'C_kec_buat_laporan/view_tambah_gambar';
	$route['buat-laporan-gambar/(:any)/(:any)'] = 'C_kec_buat_laporan/view_tambah_gambar';
//BUAT LAPORAN KECAMATAN

//BUAT DETAIL LAPORAN KECAMATAN
	$route['buat-detail-laporan'] = "C_kec_laporan/index";
	$route['buat-detail-laporan/(:any)'] = 'C_kec_laporan/index';
	$route['buat-detail-laporan/(:any)/(:any)'] = 'C_kec_laporan/index';

	$route['buat-detail-laporan-simpan'] = "C_kec_laporan/simpan";
	$route['buat-detail-laporan-simpan/(:any)'] = 'C_kec_laporan/simpan';

	$route['buat-detail-laporan-hapus'] = "C_kec_laporan/hapus";
	$route['buat-detail-laporan-hapus/(:any)'] = 'C_kec_laporan/hapus';
	$route['buat-detail-laporan-hapus/(:any)/(:any)'] = 'C_kec_laporan/hapus';
	
	$route['view-edit-detail-laporan'] = "C_kec_laporan/view_edit";
	$route['view-edit-detail-laporan/(:any)'] = 'C_kec_laporan/view_edit';
	$route['view-edit-detail-laporan/(:any)/(:any)'] = 'C_kec_laporan/view_edit';
	
	$route['hapus-detail-laporan'] = "C_kec_laporan/hapus";
	$route['hapus-detail-laporan/(:any)'] = 'C_kec_laporan/hapus';
	$route['hapus-detail-laporan/(:any)/(:any)'] = 'C_kec_laporan/hapus';
	
	
	
//BUAT DETAIL LAPORAN KECAMATAN

//PROFILE
	$route['kec-profile'] = "C_kec_profile/index";
	$route['kec-profile/(:any)'] = 'C_kec_profile/index';
	$route['kec-profile/(:any)/(:any)'] = 'C_kec_profile/index';
//PROFILE


	$route['detail-persetase-laporan'] = "C_kec_persen_laporan/index";
	$route['detail-persetase-laporan/(:any)'] = 'C_kec_persen_laporan/index';
	
//PROFILE KECAMATAN
	$route['data-kecamatan'] = "C_kec_data_kecamatan/index";
	$route['data-kecamatan/(:any)'] = 'C_kec_data_kecamatan/index';
	
	$route['data-kecamatan-simpan'] = "C_kec_data_kecamatan/simpan";
	$route['data-kecamatan-simpan/(:any)'] = 'C_kec_data_kecamatan/simpan';
	
	$route['data-kecamatan-hapus'] = "C_kec_data_kecamatan/hapus";
	$route['data-kecamatan-hapus/(:any)'] = 'C_kec_data_kecamatan/hapus';
	
	$route['data-kecamatan-desa'] = "C_kec_data_desa/index";
	$route['data-kecamatan-desa/(:any)'] = 'C_kec_data_desa/index';
	
	$route['data-kecamatan-desa-simpan'] = "C_kec_data_desa/simpan";
	$route['data-kecamatan-desa-simpan/(:any)'] = 'C_kec_data_desa/simpan';
	
	$route['data-kecamatan-desa-hapus'] = "C_kec_data_desa/hapus";
	$route['data-kecamatan-desa-hapus/(:any)'] = 'C_kec_data_desa/hapus';
	$route['data-kecamatan-desa-hapus/(:any)/(:any)'] = 'C_kec_data_desa/hapus';
//PROFILE KECAMATAN

$route['gl-admin-images'] = "C_gl_admin_images/index";
$route['gl-admin-images/(:any)'] = 'C_gl_admin_images/index';
$route['gl-admin-images/(:any)/(:any)'] = 'C_gl_admin_images/index';
$route['gl-admin-images/(:any)/(:any)/(:any)'] = 'C_gl_admin_images/index';

$route['gl-admin-images-simpan'] = "C_gl_admin_images/simpan";
$route['gl-admin-images-simpan/(:any)'] = 'C_gl_admin_images/simpan';
$route['gl-admin-images-simpan/(:any)/(:any)'] = 'C_gl_admin_images/simpan';
$route['gl-admin-images-simpan/(:any)/(:any)/(:any)'] = 'C_gl_admin_images/simpan';

$route['gl-admin-images-hapus'] = "C_gl_admin_images/hapus";
$route['gl-admin-images-hapus/(:any)'] = 'C_gl_admin_images/hapus';
$route['gl-admin-images-hapus/(:any)/(:any)'] = 'C_gl_admin_images/hapus';
$route['gl-admin-images-hapus/(:any)/(:any)/(:any)'] = 'C_gl_admin_images/hapus';

$route['gl-admin-list-catatan-laporan'] = "C_admin_persen_laporan/list_catatan_laporan";
$route['gl-admin-list-catatan-laporan/(:any)'] = 'C_admin_persen_laporan/list_catatan_laporan';

$route['gl-admin-list-rangking-kecamatan'] = "C_admin_persen_laporan/list_rangking_kecamatan";
$route['gl-admin-list-rangking-kecamatan/(:any)'] = 'C_admin_persen_laporan/list_rangking_kecamatan';

$route['gl-admin-akumulasi-rangking-kecamatan'] = "C_admin_persen_laporan/akumulasi_rangking";
$route['gl-admin-akumulasi-rangking-kecamatan/(:any)'] = 'C_admin_persen_laporan/akumulasi_rangking';
	
	









