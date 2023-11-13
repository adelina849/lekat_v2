<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_training_event extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_gl_training','M_gl_training_event','M_gl_karyawan_training'));
		
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
				
				$id_training = $this->uri->segment(2,0);
				$data_training = $this->M_gl_training->get_training('id_training',$id_training);
				if(!empty($data_training))
				{
					$data_training = $data_training->row();
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "WHERE A.id_training = '".$id_training."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND (A.nama_event LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.panitia LIKE '%".str_replace("'","",$_GET['cari'])."%')
								";
					}
					else
					{
						$cari = "WHERE A.id_training = '".$id_training."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
								";
					}
					
					
					$this->load->library('pagination');
					
					$config['first_url'] = site_url('gl-admin-training-event/'.$id_training.'?'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-admin-training-event/'.$id_training);
					
					$config['total_rows'] = $this->M_gl_training_event->count_training_event_limit($cari)->JUMLAH;
					$config['uri_segment'] = 3;	
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
					
					$list_training_event = $this->M_gl_training_event->list_training_event_limit($cari,$config['per_page'],$this->uri->segment(3,0));
					
					$msgbox_title = " Event Training ".$data_training->nama_training;
					
					$data = array('page_content'=>'gl_admin_training_event','halaman'=>$halaman,'list_training_event'=>$list_training_event,'msgbox_title' => $msgbox_title,'data_training' => $data_training);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-training');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function simpan()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."';");
			
			if(!empty($cek_ses_login))
			{
				$id_training = $this->uri->segment(2,0);
				$data_training = $this->M_gl_training->get_training('id_training',$id_training);
				if(!empty($data_training))
				{
					//echo $_POST['lamar_via'];
					if (!empty($_POST['stat_edit']))
					{	
						
						if (empty($_FILES['foto']['name']))
						{
							$this->M_gl_training_event->edit
							(
								$_POST['id_training_event'],
								$_POST['id_training'],
								$_POST['nama_event'],
								$_POST['panitia'],
								$_POST['tgl_event'],
								$_POST['tgl_selesai'],
								$_POST['alamat'],
								$_POST['ket_event'],
								$_POST['longi'],
								$_POST['lati'],
								"", //$_POST['foto'],
								"", //$_POST['foto_url'],
								$this->session->userdata('ses_id_karyawan'),
								$this->session->userdata('ses_kode_kantor')
							);
						}
						else
						{	
							/*DAPATKAN DATA TRAINING EVENT*/
								$data_training_event = $this->M_gl_training_event->get_training_event("id_training_event",$_POST['id_training_event']);
								$data_training_event = $data_training_event->row();
							/*DAPATKAN DATA TRAINING EVENT*/
					
							/*AMBIL EXT*/
								$path = $_FILES['foto']['name'];
								$ext = pathinfo($path, PATHINFO_EXTENSION);
							/*AMBIL EXT*/

							/*PROSES UPLOAD*/
								$lokasi_gambar_disimpan = 'assets/global/training/';					
								$this->M_gl_pengaturan->do_upload_global($lokasi_gambar_disimpan,$_POST['id_training_event'].'.'.$ext,$data_training_event->foto);
							/*PROSES UPLOAD*/
							
							$this->M_gl_training_event->edit
							(
								
								$_POST['id_training_event'],
								$_POST['id_training'],
								$_POST['nama_event'],
								$_POST['panitia'],
								$_POST['tgl_event'],
								$_POST['tgl_selesai'],
								$_POST['alamat'],
								$_POST['ket_event'],
								$_POST['longi'],
								$_POST['lati'],
								$_POST['id_training_event'].'.'.$ext, //$_POST['foto'],
								$lokasi_gambar_disimpan, //$_POST['foto_url'],
								$this->session->userdata('ses_id_karyawan'),
								$this->session->userdata('ses_kode_kantor')
							);
						}
						
						/* CATAT AKTIFITAS EDIT*/
						if($this->session->userdata('catat_log') == 'Y')
						{
							$this->M_gl_log->simpan_log
							(
								$this->session->userdata('ses_id_karyawan'),
								'UPDATE',
								'Melakukan perubahan data training event '.$_POST['nama_event'].' | '.$_POST['alamat'],
								$this->M_gl_pengaturan->getUserIpAddr(),
								gethostname(),
								$this->session->userdata('ses_kode_kantor')
							);
						}
						/* CATAT AKTIFITAS EDIT*/
					}
					else
					{
						if (empty($_FILES['foto']['name']))
						{
							$foto = "";
							$lokasi_gambar_disimpan = "";
						}
						else
						{
							/*AMBIL EXT*/
								$path = $_FILES['foto']['name'];
								$ext = pathinfo($path, PATHINFO_EXTENSION);
							/*AMBIL EXT*/

							/*PROSES UPLOAD*/
								$lokasi_gambar_disimpan = 'assets/global/training/';
								$foto = str_replace(" ","",$_FILES['foto']['name']);							
								
								$this->M_gl_pengaturan->do_upload_global($lokasi_gambar_disimpan,$foto,"");
							/*PROSES UPLOAD*/
						}
						
						$this->M_gl_training_event->simpan
						(
							
							$_POST['id_training'],
							$_POST['nama_event'],
							$_POST['panitia'],
							$_POST['tgl_event'],
							$_POST['tgl_selesai'],
							$_POST['alamat'],
							$_POST['ket_event'],
							$_POST['longi'],
							$_POST['lati'],
							$foto,
							$lokasi_gambar_disimpan,
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						);
						
					}
					
					header('Location: '.base_url().'gl-admin-training-event/'.$id_training);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-training');
				}
		
			}
		}			
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$id_training = $this->uri->segment(2,0);
		$data_training = $this->M_gl_training->get_training('id_training',$id_training);
		if(!empty($data_training))
		{
			$id_training_event = $this->uri->segment(3,0);
			$data_training_event = $this->M_gl_training_event->get_training_event('id_training_event',$id_training_event);
			if(!empty($data_training_event))
			{
			
				$data_training_event = $data_training_event->row();
				
				//HAPUS EVENT
					$this->M_gl_training_event->hapus("id_training_event",$id_training_event);
				//HAPUS EVENT
				
				//HAPUS PESERTA
					$this->M_gl_karyawan_training->hapus_2("id_training_event",$id_training_event);
				//HAPUS PESERTA
				
				/*HAPUS GAMBAR DARI SERVER*/
					$lokasi_gambar_disimpan = 'assets/global/training/';
					$foto = $data_training_event->foto;							
					
					$this->M_gl_pengaturan->do_upload_global($lokasi_gambar_disimpan,"",$foto);
				/*HAPUS GAMBAR DARI SERVER*/
							
				
				/* CATAT AKTIFITAS HAPUS*/
				if($this->session->userdata('catat_log') == 'Y')
				{
					$this->M_gl_log->simpan_log
					(
						$this->session->userdata('ses_id_karyawan'),
						'DELETE',
						'Melakukan penghapusan data event training '.$data_training_event->nama_event.' | '.$data_training_event->alamat,
						$this->M_gl_pengaturan->getUserIpAddr(),
						gethostname(),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				/* CATAT AKTIFITAS HAPUS*/
			}
		}
		
		header('Location: '.base_url().'gl-admin-training-event/'.$id_training);
	}
	
	function cek_training_event()
	{
		$hasil_cek = $this->M_gl_training_event->get_training_event('id_training_event',$_POST['id_training_event']);
		echo $hasil_cek;
	}
	
	function cek_table_peserta_training()
	{
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (COALESCE(B.no_karyawan,'') LIKE '%".$_POST['cari']."%' OR COALESCE(B.nama_karyawan,'') LIKE '%".$_POST['cari']."%')
							";
		}
		else
		{
			$cari = "AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
		}
		
		$list_peserta_event = $this->M_gl_training_event->cek_peserta_event($_POST['id_training_event'],$cari,$_POST['limit'],$_POST['offset']);
		//echo"ADA";
		
		if(!empty($list_peserta_event))
		{
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="10%">FOTO PROFILE</th>';
							echo '<th width="35%">BIODATA</th>';
							echo '<th width="35%">NILAI</th>';
				echo '</tr>
</thead>';
				$list_result = $list_peserta_event->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_'.$no.'">';
					
						echo'<td>'.$no.'</td>';
						
						
						if($row->avatar == "")
						{
							$src = base_url().'assets/global/karyawan/loading.gif';
							echo '<td><img id="IMG_'.$no.'"  width="100px" height="100px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" />
							</td>';
						}
						else
						{
							//$src = base_url().'assets/global/karyawan/'.$row->avatar;
							$src = base_url().''.$row->avatar_url.''.$row->avatar;
							echo '<td><img id="IMG_'.$no.'"  width="100px" height="100px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" />
							</td>';
						}
						echo'<td>
								<b>NO : </b>'.$row->no_karyawan.' 
								<br/> <b>NIK : </b>'.$row->nik_karyawan.' 
								<br/> <b>NAMA : </b>'.$row->nama_karyawan.'
							</td>';
						echo'<td>
								<b>NILAI : </b>'.$row->nilai.' 
								<br/> <b>CATATAN : </b>'.$row->ket_karyawan_training.'
							</td>';
							
					echo'</tr>';
					$no++;
				}
				
				echo '</tbody>';
			echo'</table>';
		}
		else
		{
			echo "TIDAK ADA DATA YANG DITAMPILKAN";
		}
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */