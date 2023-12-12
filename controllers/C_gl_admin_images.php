<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_images extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_gl_images'));
	}
	
	public function index()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_akun->get_cek_login($this->session->userdata('ses_user_admin'),md5(base64_decode($this->session->userdata('ses_pass_admin'))));
			
			if(!empty($cek_ses_login))
			{
				$group_by = $this->uri->segment(2,0);
				$id = $this->uri->segment(3,0);
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							AND group_by = '".$group_by."'
							AND id = '".$id."'
							AND A.img_nama LIKE '%".str_replace("'","",$_GET['cari'])."%' ";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							AND group_by = '".$group_by."'
							AND id = '".$id."'
							";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-images/'.$this->uri->segment(2,0).'/'.$this->uri->segment(3,0).'?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-images/'.$this->uri->segment(2,0).'/'.$this->uri->segment(3,0).'/');
				$config['total_rows'] = $this->M_gl_images->count_images_limit($cari)->JUMLAH;
				$config['uri_segment'] = 4;	
				$config['per_page'] = 30;
				$config['num_links'] = 2;
				$config['suffix'] = '?' . http_build_query($_GET, '', "&");
				//$config['use_page_numbers'] = TRUE;
				//$config['page_query_string'] = false;
				//$config['query_string_segment'] = '';
				$config['first_page'] = 'Awal';
				$config['last_page'] = 'Akhir';
				$config['next_page'] = '&laquo;';
				$config['prev_page'] = '&raquo;';
				
				
				$config['full_tag_open'] = '<div><ul class="pagination">';
				$config['full_tag_close'] = '</ul></div>';
				$config['first_link'] = '&laquo; First';
				$config['first_tag_open'] = '<li class="prev page">';
				$config['first_tag_close'] = '</li>';
				$config['last_link'] = 'Last &raquo;';
				$config['last_tag_open'] = '<li class="next page">';
				$config['last_tag_close'] = '</li>';
				$config['next_link'] = 'Next &rarr;';
				$config['next_tag_open'] = '<li class="next page">';
				$config['next_tag_close'] = '</li>';
				$config['prev_link'] = '&larr; Previous';
				$config['prev_tag_open'] = '<li class="prev page">';
				$config['prev_tag_close'] = '</li>';
				$config['cur_tag_open'] = '<li class="active"><a href="">';
				$config['cur_tag_close'] = '</a></li>';
				$config['num_tag_open'] = '<li class="page">';
				$config['num_tag_close'] = '</li>';
				
				
				//inisialisasi config
				$this->pagination->initialize($config);
				$halaman = $this->pagination->create_links();
				
				$list_images = $this->M_gl_images->list_images_limit($cari,$config['per_page'],$this->uri->segment(4,0));
				
				$msgbox_title = " Pengaturan Gambar";
				
				$data = array('page_content'=>'gl_admin_images','halaman'=>$halaman,'list_images'=>$list_images,'msgbox_title' => $msgbox_title);
				$this->load->view('kecamatan/container',$data);
			}
			else
			{
				header('Location: '.base_url().'kecamatan-login');
			}
		}
	}
	
	public function simpan()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_akun->get_cek_login($this->session->userdata('ses_user_admin'),md5(base64_decode($this->session->userdata('ses_pass_admin'))));
			
			if(!empty($cek_ses_login))
			{
				//echo $_POST['lamar_via'];
				if (!empty($_POST['stat_edit']))
				{	
					
					if (empty($_FILES['foto']['name']))
					{
						$this->M_gl_images->edit_info_saja
						(
							$_POST['stat_edit'],
							$_POST['id'],
							$_POST['group_by'],	
							$_POST['img_nama'],
							$_POST['ket_img'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					else
					{	
						
						$id_images = $this->M_gl_images->get_id_images($this->session->userdata('ses_kode_kantor'))->id_images;
						
						/*AMBIL EXT*/
							$path = $_FILES['foto']['name'];
							$ext = pathinfo($path, PATHINFO_EXTENSION);
						/*AMBIL EXT*/

						/*PROSES UPLOAD*/
							//$lokasi_gambar_disimpan = 'assets/global/laporan/';
							$lokasi_gambar_disimpan = 'assets/global/images/';
							//$avatar = $this->session->userdata('ses_kode_kantor').''.$_POST['stat_edit'];
							$avatar = $id_images.'.'.$ext;
							
							//$this->M_akun->do_upload_global_dinamic_input_name("foto",$lokasi_gambar_disimpan,$avatar,"");
							$this->do_upload_global_dinamic_input_name("foto",$lokasi_gambar_disimpan,$avatar,"",$_POST['url_link']);
							
						/*PROSES UPLOAD*/
						
						$this->M_gl_images->edit_with_images
						(
							$_POST['stat_edit'],
							$_POST['id'],
							$_POST['group_by'],
							$_POST['img_nama'],
							$_POST['ket_img'],
							$avatar, //$_POST['img_file'],
							$lokasi_gambar_disimpan, //$_POST['img_url'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					
					/* CATAT AKTIFITAS EDIT*/
					// if($this->session->userdata('catat_log') == 'Y')
					// {
						// $this->M_gl_log->simpan_log
						// (
							// $this->session->userdata('ses_id_karyawan'),
							// 'UPDATE',
							// 'Melakukan perubahan data gambar '.$_POST['img_nama'].' | '.$_POST['group_by'],
							// $this->M_akun->getUserIpAddr(),
							// gethostname(),
							// $this->session->userdata('ses_kode_kantor')
						// );
					// }
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					$id_images = $this->M_gl_images->get_id_images($this->session->userdata('ses_kode_kantor'))->id_images;
					
					//echo $id_images;
					if (empty($_FILES['foto']['name']))
					{
						$avatar = "";
						$lokasi_gambar_disimpan = "";
					}
					else
					{
						/*AMBIL EXT*/
							$path = $_FILES['foto']['name'];
							$ext = pathinfo($path, PATHINFO_EXTENSION);
						/*AMBIL EXT*/

						/*PROSES UPLOAD*/
							//$lokasi_gambar_disimpan = 'assets/global/laporan/';
							$lokasi_gambar_disimpan = 'assets/global/images/';
							//$avatar = str_replace(" ","",$_FILES['foto']['name']);
							//$avatar = $avatar.'.'.$ext;
							$avatar = $id_images.'.'.$ext;
							
							//$this->M_akun->do_upload_global($lokasi_gambar_disimpan,$avatar,"");
							//$this->M_akun->do_upload_global_dinamic_input_name("foto",$lokasi_gambar_disimpan,$avatar,"");
							$this->do_upload_global_dinamic_input_name("foto",$lokasi_gambar_disimpan,$avatar,"",$_POST['url_link']);
						/*PROSES UPLOAD*/
					}
					
					$this->M_gl_images->simpan
					(
						$_POST['id'],
						$_POST['group_by'],
						$_POST['img_nama'],
						$avatar, //$_POST['img_file'],
						$lokasi_gambar_disimpan, //$_POST['img_url'],
						$_POST['ket_img'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
						
					);
				}
				header('Location: '.base_url().'gl-admin-images/'.$_POST['group_by'].'/'.$_POST['id']);
			}
		}			
		//echo 'ade';*/
	}

	public function hapus()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_akun->get_cek_login($this->session->userdata('ses_user_admin'),md5(base64_decode($this->session->userdata('ses_pass_admin'))));
			
			if(!empty($cek_ses_login))
			{
				
				$IMG_GRUP = $this->uri->segment(2,0);
				$ID = $this->uri->segment(3,0);
				$IMG_ID = $this->uri->segment(4,0);
				$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
											AND group_by = '".$this->uri->segment(2,0)."'
											AND id = '".$this->uri->segment(3,0)."'
											AND id_images = '".$IMG_ID."'";
											
				$hasil_cek = $this->M_gl_images->get_images_cari($cari);
				if(!empty($hasil_cek))
				{
					//$hasil_cek = $hasil_cek->row();
					//$this->M_akun->do_upload_global(base_url().'/'.$hasil_cek->img_url,"",$hasil_cek->img_file);
					
					$this->M_akun->do_upload_global_dinamic_input_name('',$hasil_cek->img_url,"",$hasil_cek->img_file);
					$this->M_gl_images->hapus('id_images',$IMG_ID);
					
					/* CATAT AKTIFITAS EDIT*/
					if($this->session->userdata('catat_log') == 'Y')
					{
						$this->M_gl_log->simpan_log
						(
							$this->session->userdata('ses_id_karyawan'),
							'UPDATE',
							'Melakukan pengahpusan data gambar '.$hasil_cek->group_by.' dengan nama '.$hasil_cek->img_nama.' | '.$hasil_cek->ket_img,
							$this->M_akun->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				header('Location: '.base_url().'gl-admin-images/'.$IMG_GRUP.'/'.$ID);
			}
		}
	}
	
	function do_upload_global_dinamic_input_name($nama_input,$lokasi,$nama_file,$cek_bfr,$url_link)
	{
		$this->load->library('upload');
		
		if($cek_bfr != '')
		{
			//@unlink('./assets/global/karyawan/'.$cek_bfr);
			//NB : unlink jangan pakai base_url()
			@chmod($lokasi.''.$cek_bfr, 0777);
			@unlink($lokasi.''.$cek_bfr);
		}
		
		if (!empty($_FILES[$nama_input]['name']))
		{
			//$config['upload_path'] = 'assets/global/karyawan/';
			$config['upload_path'] = $lokasi;
			$config['allowed_types'] = 'gif|jpg|jpeg|png|mp4';
			//$config['allowed_types'] = 'gif|jpg|jpeg|png';
			$config['max_size']	= '2024';
			$config['create_thumb'] = TRUE;
			$config['maintain_ratio'] = TRUE;
			$config['maintain_ratio'] = TRUE;
			$config['quality']= '50%';
			//$config['max_widtd']  = '300';
			//$config['max_height']  = '300';
			$config['file_name']	= $nama_file;
			$config['overwrite']	= true;
			

			$this->upload->initialize($config);
			
			/*
			//Upload file 1
			if ($this->upload->do_upload($nama_input))
			{
				$hasil = $this->upload->data();
			}
			*/
			
			//if (!$this->upload->do_upload('file')) 
			if (!$this->upload->do_upload($nama_input)) 
			{
				$error = array('error' => $this->upload->display_errors());
				//$this->session->set_flashdata('msg','Ada kesalah dalam upload'); 
				
				//$this->session->set_flashdata('msg', '<div class="alert alert-danger"><b>PROSES IMPORT GAGAL ! </b> '.$this->upload->display_errors().' ('.$pesan_pasien.')</div>');
				$this->session->set_flashdata('msg', '<div class="alert alert-danger"><b>PROSES IMPORT GAGAL ! </b> '.$this->upload->display_errors().' </div>');
				
				//echo $this->session->flashdata('msg');
				//redirect('gl-admin-images'); 
				redirect($url_link); 
			}
			else
			{
				$hasil = $this->upload->data();
				
				/*
				$arr_nama_file = explode(".", $nama_file);
				$ext = $arr_nama_file[1];
				if($ext == 'png')
				{
					$this->hs_png2webp($lokasi.''.$nama_file, $lokasi.''.$arr_nama_file[0].'.webp');
				}
				elseif($ext == 'jpg')
				{
					$this->hs_jpg2webp($lokasi.''.$nama_file, $lokasi.''.$arr_nama_file[0].'.webp');
				}
				elseif($ext == 'jpeg')
				{
					$this->hs_jpg2webp($lokasi.''.$nama_file, $lokasi.''.$arr_nama_file[0].'.webp');
				}
				*/
				
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_images.php */