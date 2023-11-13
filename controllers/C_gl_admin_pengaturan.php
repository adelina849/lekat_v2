<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_pengaturan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_pengaturan'));
		
	}
	
	public function index()
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
				/*
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (kode_penyakit LIKE '%".str_replace("'","",$_GET['cari'])."%' OR nama_penyakit LIKE '%".str_replace("'","",$_GET['cari'])."%')";
				}
				else
				{
					$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				*/
				
				$list_pengaturan = $this->M_gl_pengaturan->list_pengaturan();
				
				$msgbox_title = " Pengaturan Aplikasi ";
				
				$data = array('page_content'=>'gl_admin_pengaturan','list_pengaturan'=>$list_pengaturan,'msgbox_title' => $msgbox_title);
				
				//$this->load->view('admin/container',$data);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function updatePengaturan()
	{
		$this->M_gl_pengaturan->ubahPengaturanSatuan($_POST['id_pengaturan'],$_POST['nilai']);
		echo('Berhasil');
	}
	
	function notifikasi_pasien_jumlah_saja()
	{
		$jumlah_pasien = $this->M_gl_log->notif_count_pasien_dokter($this->session->userdata('ses_kode_kantor'),$this->session->userdata('ses_id_karyawan'),$this->session->userdata('ses_nama_jabatan'));
				
		if(($jumlah_pasien > 0) || ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi'))
		{
			echo $jumlah_pasien;
		}
		else
		{
			echo 0;
		}
	}
	
	function notifikasi_pasien()
	{
		echo'<a href="#" class="dropdown-toggle" data-toggle="dropdown">';
		  echo'<i class="fa fa-bell-o"></i>';
		  
				$jumlah_pasien = $this->M_gl_log->notif_count_pasien_dokter($this->session->userdata('ses_kode_kantor'),$this->session->userdata('ses_id_karyawan'),$this->session->userdata('ses_nama_jabatan'));
				
				if(($jumlah_pasien > 0) || ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi'))
				{
					echo'<span class="label label-danger">'.$jumlah_pasien.'</span>';
				}
				else
				{
					echo'<span class="label label-danger">0</span>';
				}
		  
		  
		echo'</a>';
		echo'<ul class="dropdown-menu">';
			
			if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
			{
				echo'<li style="color:red;background-color:purple;" class="header">'.$this->session->userdata('ses_nama_karyawan').' ada '.$jumlah_pasien.' Pelanggan yang menunggu</li>';
			}
			else
			{
				echo'<li style="color:red;background-color:purple;" class="header">'.$this->session->userdata('ses_nama_karyawan').' ada '.$jumlah_pasien.' pasien yang menunggu</li>';
			}
		  
		  echo'<li>';
			echo'<!-- inner menu: contains the actual data -->';
			echo'<ul class="menu"  style="background-color:#F0FFFF;">';
			echo'<!-- <span id="notif_list_pasien_tunggu"> -->';
				
					if($jumlah_pasien > 0)
					{
						$notif_list_pasien_tunggu = $this->M_gl_log->notif_list_pasien($this->session->userdata('ses_kode_kantor'),$this->session->userdata('ses_id_karyawan'),$this->session->userdata('ses_nama_jabatan'));
						
						if(!empty($notif_list_pasien_tunggu))
						{
							$list_result = $notif_list_pasien_tunggu->result();
							//echo '<tbody>';
							foreach($list_result as $row)
							{
								if($row->avatar == "")
								{
									$src = base_url().'assets/global/costumer/loading.gif';
								}
								else
								{
									if(!(file_exists("assets/global/costumer/".$row->avatar)))
									{
										$src = base_url().'assets/global/costumer/loading.gif';
									}
									else
									{
										$src = base_url().''.$row->avatar_url.''.$row->avatar;
									}
									
								}
								
								echo'<li><!-- start message -->';
									echo'<a href="'.base_url().'gl-admin-pemeriksaan-dokter-proses/'.md5($row->id_h_penjualan).'">';
										echo'<div class="pull-left">';
											echo'<img src="'.$src.'" class="img-circle" alt="User Image">';
										echo'</div>';
									
									echo'<h5>';
										echo $row->type_h_penjualan;
									echo'</h5>';
									echo'<h4>';
										echo $row->nama_costumer;
										echo'<small><i class="fa fa-clock-o"></i> '.$row->MENIT_TUNGGU.' Menit</small>';
									echo'</h4>';
									echo'<p>'.word_limiter($row->ket_penjualan,10).'</p>';
									echo'</a>';
								echo'</li>';
								
							}
							//echo '</tbody>';
						}
						else
						{
							echo'<center>';
							echo'Tidak Ada Data Yang Ditampilkan !';
							echo'</center>';
						}
					}
			
			  
		echo'<!--</span>-->';
		echo'</ul>';
	  echo'</li>';
	  echo'</ul>';
	}

	function backup_database()
	{
		$this->load->dbutil();
		$this->load->helper('file');
		
		$config = array(
			'format'	=> 'zip',
			'filename'	=> 'database.sql'
		);
		
		$backup =& $this->dbutil->backup($config);
		//$save = FCPATH.'data/backup-'.date("ymdHis").'-db.zip';
		$save = FCPATH.'data/backup-'.date("ymdHis").'-db.zip';
		write_file($save, $backup);
		$this->load->helper('download');
		force_download(date("ymdHis").'-db.zip', $backup);
	}
	
	function get_notifikasi_transaksi()
	{
		$data_transaksi = $this->M_gl_pengaturan->get_notifikasi_transaksi($this->session->userdata('ses_kode_kantor'));
		if(!empty($data_transaksi))
		{
			$data_transaksi = $data_transaksi->row();
			echo $data_transaksi->PENDING.'|'.$data_transaksi->PEMERIKSAAN.'|'.$data_transaksi->PEMBAYARAN.'|'.$data_transaksi->SUM_ALL;
		}
		else
		{
			echo "0|0|0|0";
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_pengaturan.php */