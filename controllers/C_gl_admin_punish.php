<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_punish extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_gl_punish','M_gl_karyawan','M_gl_peraturan'));
		
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
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (COALESCE(A.no_pelanggaran,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' OR COALESCE(B.no_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' OR COALESCE(B.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'  OR COALESCE(C.kode_per,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'  OR COALESCE(C.nama_per,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/training?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/training/';
				
				$config['first_url'] = site_url('gl-admin-punishment?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-punishment/');
				$config['total_rows'] = $this->M_gl_punish->count_punish_limit($cari)->JUMLAH;
				$config['uri_segment'] = 2;	
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
				
				
				$list_punish = $this->M_gl_punish->list_punish_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$msgbox_title = " Punishment Karyawan";
				
				$data = array('page_content'=>'gl_admin_punish','halaman'=>$halaman,'list_punish'=>$list_punish,'msgbox_title' => $msgbox_title);
				$this->load->view('admin/container',$data);
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
				if (!empty($_POST['stat_edit']))
				{	
					$this->M_gl_punish->edit
					(
						$_POST['stat_edit'],
						$_POST['id_karyawan'],
						$_POST['id_per'],
						$_POST['no_pelanggaran'],
						$_POST['nama_pelanggaran'],
						$_POST['hukuman'],
						$_POST['tgl_mulai'],
						$_POST['tgl_selesai'],
						$_POST['kronologi'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					);
					
					/* CATAT AKTIFITAS EDIT*/
					if($this->session->userdata('catat_log') == 'Y')
					{
						$this->M_gl_log->simpan_log
						(
							$this->session->userdata('ses_id_karyawan'),
							'UPDATE',
							'Melakukan perubahan data perubahan Punishment '.$_POST['no_pelanggaran'].'  '.$_POST['nama_pelanggaran'].' | '.$_POST['hukuman'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					$this->M_gl_punish->simpan
					(
					
						$_POST['id_karyawan'],
						$_POST['id_per'],
						$_POST['no_pelanggaran'],
						$_POST['nama_pelanggaran'],
						$_POST['hukuman'],
						$_POST['tgl_mulai'],
						$_POST['tgl_selesai'],
						$_POST['kronologi'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				
				header('Location: '.base_url().'gl-admin-punishment');
		
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}			
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$id_punish = ($this->uri->segment(2,0));
		$data_punish = $this->M_gl_punish->get_punish('id_punish',$id_punish);
		if(!empty($data_punish))
		{
			$data_punish = $data_punish->row();
			
			//HAPUS TRAINING
				$this->M_gl_punish->hapus('id_punish',$id_punish);
			//HAPUS TRAINING
			
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data perubahan jabatan (promosi,demosi,rotasi dan probation) '.$data_punish->no_surat.' | '.$data_punish->periode,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-punishment');
	}
	
	function list_karyawan()
	{
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (COALESCE(A.no_karyawan,'') LIKE '%".$_POST['cari']."%' OR COALESCE(A.nama_karyawan,'') LIKE '%".$_POST['cari']."%')
							";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
		}
		
		
		$list_karyawan = $this->M_gl_karyawan->list_karyawan_limit($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_karyawan))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="10%">FOTO PROFILE</th>';
							echo '<th width="55%">BIODATA</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_karyawan->result();
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
								<br/> <b>TTL : </b>'.$row->tmp_lahir.','.$row->tgl_lahir.'
								<br/> <b>KELAMIN : </b>'.$row->kelamin.'
								<br/> <b>ALAMAT : </b>'.$row->alamat.'
							</td>';
						echo'<td>
								<button type="button" onclick="insert_karyawan('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button>
							</td>';
						echo'<input type="hidden" id="id_karyawan_3_'.$no.'" name="id_karyawan_3_'.$no.'" value="'.$row->id_karyawan.'" />';
						echo'<input type="hidden" id="no_karyawan_3_'.$no.'" name="no_karyawan_3_'.$no.'" value="'.$row->no_karyawan.'" />';
						echo'<input type="hidden" id="nama_karyawan_3_'.$no.'" name="nama_karyawan_3_'.$no.'" value="'.$row->nama_karyawan.'" />';
					echo'</tr>';
					$no++;
				}
				
				echo '</tbody>';
			echo'</table>';
			echo'</div>';
		}
		else
		{
			echo "TIDAK ADA DATA YANG DITAMPILKAN";
		}
	}
	
	function list_peraturan()
	{
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (kode_per LIKE '%".$_POST['cari']."%' OR nama_per LIKE '%".$_POST['cari']."%')
							";
		}
		else
		{
			$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
		}
		
		
		$list_peraturan = $this->M_gl_peraturan->list_peraturan_limit($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_peraturan))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="20%">KODE</th>';
							echo '<th width="50%">NAMA</th>';
							echo '<th width="10%">BOBOT</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_peraturan->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_'.$no.'">';
					
						echo'<td>'.$no.'</td>';
						echo'<td>'.$row->kode_per.'</td>';
						echo'<td>'.$row->nama_per.'</td>';
						echo'<td>'.$row->bobot_per.'</td>';
						echo'<td>
								<button type="button" onclick="insert_peraturan('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button>
							</td>';
						
						echo'<input type="hidden" id="id_per_4_'.$no.'" name="id_per_4_'.$no.'" value="'.$row->id_per.'" />';
						echo'<input type="hidden" id="kode_per_4_'.$no.'" name="kode_per_4_'.$no.'" value="'.$row->kode_per.'" />';
						echo'<input type="hidden" id="nama_per_4_'.$no.'" name="nama_per_4_'.$no.'" value="'.$row->nama_per.'" />';
						echo'<input type="hidden" id="bobot_per_4_'.$no.'" name="bobot_per_4_'.$no.'" value="'.$row->bobot_per.'" />';
						echo'<input type="hidden" id="ket_per_4_'.$no.'" name="ket_per_4_'.$no.'" value="'.$row->ket_per.'" />';
						echo'<input type="hidden" id="user_ins_4_'.$no.'" name="user_ins_4_'.$no.'" value="'.$row->user_ins.'" />';
						echo'<input type="hidden" id="user_updt_4_'.$no.'" name="user_updt_4_'.$no.'" value="'.$row->user_updt.'" />';
						echo'<input type="hidden" id="tgl_ins_4_'.$no.'" name="tgl_ins_4_'.$no.'" value="'.$row->tgl_ins.'" />';
						echo'<input type="hidden" id="tgl_updt_4_'.$no.'" name="tgl_updt_4_'.$no.'" value="'.$row->tgl_updt.'" />';
						echo'<input type="hidden" id="kode_kantor_4_'.$no.'" name="kode_kantor_4_'.$no.'" value="'.$row->kode_kantor.'" />';

					echo'</tr>';
					$no++;
				}
				
				echo '</tbody>';
			echo'</table>';
			echo'</div>';
		}
		else
		{
			echo "TIDAK ADA DATA YANG DITAMPILKAN";
		}
	}
	
	function cek_punish()
	{
		$hasil_cek = $this->M_gl_punish->get_punish('kode_promosi',$_POST['kode_promosi']);
		echo $hasil_cek;
	}
	
	function ubah_proses_karyawan_promo()
	{
		$hasil_cek = $this->M_gl_punish->edit_ubah_nilai($this->session->userdata('ses_kode_kantor'),$_POST['id_kj'],$_POST['nilai'],$_POST['status'],$_POST['ket_nilai']);
		
		if($hasil_cek == "BERHASIL")
		{
			echo $hasil_cek;
		}
		else
		{
			false;
		}
		
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_training.php */