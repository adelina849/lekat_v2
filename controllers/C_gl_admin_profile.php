<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_profile extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_gl_karyawan'));
		
	}
	
	public function simpan()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				//echo $_POST['lamar_via'];
					if (empty($_FILES['prof_foto']['name']))
					{
						$this->M_gl_karyawan->edit_profile
						(
							$_POST['prof_id_karyawan'],
							$_POST['prof_nama_karyawan'],
							$_POST['prof_pnd'],
							$_POST['prof_tlp'],
							$_POST['prof_email'],
							$_POST['prof_tmp_lahir'],
							$_POST['prof_tgl_lahir'],
							$_POST['prof_kelamin'],
							$_POST['prof_sts_nikah'],
							"",
							"",
							$_POST['prof_alamat'],
							$_POST['prof_user'],
							$_POST['prof_pass'],
							$_POST['prof_pass2'],
							$this->session->userdata('ses_kode_kantor')
						
							
						);
					}
					else
					{	
						/*DAPATKAN DATA supplier*/
							$data_karyawan = $this->M_gl_karyawan->get_karyawan("id_karyawan",$_POST['prof_id_karyawan']);
							$data_karyawan = $data_karyawan->row();
						/*DAPATKAN DATA supplier*/
				
						/*AMBIL EXT*/
							$path = $_FILES['prof_foto']['name'];
							$ext = pathinfo($path, PATHINFO_EXTENSION);
						/*AMBIL EXT*/

						/*PROSES UPLOAD*/
							$lokasi_gambar_disimpan = 'assets/global/karyawan/';						
							$this->M_gl_pengaturan->do_upload_global_dinamic_input_name('prof_foto',$lokasi_gambar_disimpan,$_POST['prof_id_karyawan'].'.'.$ext,$data_karyawan->avatar);
						/*PROSES UPLOAD*/
						
						$this->M_gl_karyawan->edit_profile
						(
							$_POST['prof_id_karyawan'],
							$_POST['prof_nama_karyawan'],
							$_POST['prof_pnd'],
							$_POST['prof_tlp'],
							$_POST['prof_email'],
							$_POST['prof_tmp_lahir'],
							$_POST['prof_tgl_lahir'],
							$_POST['prof_kelamin'],
							$_POST['prof_sts_nikah'],
							$_POST['prof_id_karyawan'].'.'.$ext,
							$lokasi_gambar_disimpan,
							$_POST['prof_alamat'],
							$_POST['prof_user'],
							$_POST['prof_pass'],
							$_POST['prof_pass2'],
							$this->session->userdata('ses_kode_kantor')
						
							
						);
					
					
					/* CATAT AKTIFITAS EDIT*/
					if($this->session->userdata('catat_log') == 'Y')
					{
						$this->M_gl_log->simpan_log
						(
							$this->session->userdata('ses_id_supplier'),
							'UPDATE',
							'Melakukan perubahan data profile '.$_POST['prof_nama_karyawan'].' | '.$_POST['prof_nama_karyawan'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				
				$this->session->unset_userdata('ses_nik_karyawan');
				$this->session->unset_userdata('ses_nama_karyawan');
				$this->session->unset_userdata('ses_pnd');
				$this->session->unset_userdata('ses_tlp');
				$this->session->unset_userdata('ses_email');
				$this->session->unset_userdata('ses_tmp_lahir');
				$this->session->unset_userdata('ses_tgl_lahir');
				$this->session->unset_userdata('ses_kelamin');
				$this->session->unset_userdata('ses_sts_nikah');
				$this->session->unset_userdata('ses_alamat');
				$this->session->unset_userdata('ses_avatar_url');
				
				if($_POST['prof_pass2'] == "")
				{
					$data_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.id_karyawan = '".$this->session->userdata('ses_id_karyawan')."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."';");
					if(!empty($data_login))
					{
						if ($data_login->avatar <> "")
						{
							//$src = $data_login->avatar_url;
							//$src = base_url().'assets/global/karyawan/'.$data_login->avatar;
							$src = base_url().''.$data_login->avatar_url.''.$data_login->avatar;
						}
						else
						{
							$src = base_url().'assets/global/users/loading.gif';
						}
						
						$user = array(
							'ses_nik_karyawan' => $data_login->nik_karyawan,
							'ses_nama_karyawan' => $data_login->nama_karyawan,
							'ses_pnd' => $data_login->pnd,
							'ses_tlp' => $data_login->tlp,
							'ses_email' => $data_login->email,
							'ses_tmp_lahir' => $data_login->tmp_lahir,
							'ses_tgl_lahir' => $data_login->tgl_lahir,
							'ses_kelamin' => $data_login->kelamin,
							'ses_sts_nikah' => $data_login->sts_nikah,
							'ses_avatar_url' => $src,
							'ses_alamat' => $data_login->alamat
						);
						$this->session->set_userdata($user);
						
						header('Location: '.base_url().'gl-admin');
					}
					else
					{
						header('Location: '.base_url());
					}
				}
				else
				{
					header('Location: '.base_url());
				}
				
				
				
		
			}
		}			
		//echo 'ade';*/
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */