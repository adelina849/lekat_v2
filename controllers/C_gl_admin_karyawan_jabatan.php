<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_karyawan_jabatan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_gl_karyawan_jabatan','M_gl_karyawan','M_gl_jabatan','M_gl_dept'));
		
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
				if((!empty($_GET['kontrak'])) && ($_GET['kontrak']== "YA")  )
				{
					$kontrak = " AND DATE_ADD(DATE(NOW()), INTERVAL 30 DAY) >= DATE(A.tgl_sampai)";
				}
				else
				{
					$kontrak = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							".$kontrak."
							AND (COALESCE(B.no_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' OR COALESCE(B.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%')";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ".$kontrak."";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/training?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/training/';
				
				$config['first_url'] = site_url('gl-admin-promosi?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-promosi/');
				$config['total_rows'] = $this->M_gl_karyawan_jabatan->count_karyawan_jabatan_limit($cari)->JUMLAH;
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
				
				$list_jabatan  = $this->M_gl_jabatan->list_jabatan(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'",200,0);
				
				$list_dept = $this->M_gl_dept->list_dept_limit(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'",200,0);
				
				$list_karyawan_jabatan = $this->M_gl_karyawan_jabatan->list_karyawan_jabatan_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$msgbox_title = " Probation,Promosi,Rotasi dan Demosi";
				
				$data = array('page_content'=>'gl_admin_karyawan_jabatan','halaman'=>$halaman,'list_karyawan_jabatan'=>$list_karyawan_jabatan,'msgbox_title' => $msgbox_title,'list_jabatan' => $list_jabatan,'list_dept' => $list_dept);
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
					$this->M_gl_karyawan_jabatan->edit
					(
						$_POST['stat_edit'],
						$_POST['id_karyawan'],
						$_POST['id_jabatan'],
						$_POST['id_dept'],
						$_POST['kode_promosi'],
						$_POST['no_surat'],
						$_POST['periode'],
						$_POST['rekomendasi'],
						$_POST['tgl_dari'],
						$_POST['tgl_sampai'],
						$_POST['keterangan'],
						$_POST['tipe'],
						$_POST['masa_percobaan'],
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
							'Melakukan perubahan data perubahan jabatan (promosi,demosi,rotasi dan probation) '.$_POST['tipe'].'  '.$_POST['no_surat'].' | '.$_POST['periode'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					$this->M_gl_karyawan_jabatan->simpan
					(
					
						$_POST['id_karyawan'],
						$_POST['id_jabatan'],
						$_POST['id_dept'],
						$_POST['kode_promosi'],
						$_POST['no_surat'],
						$_POST['periode'],
						$_POST['rekomendasi'],
						$_POST['tgl_dari'],
						$_POST['tgl_sampai'],
						$_POST['keterangan'],
						$_POST['tipe'],
						$_POST['masa_percobaan'],
						"PENGAJUAN",
						"PENGAJUAN",
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				
				header('Location: '.base_url().'gl-admin-promosi');
		
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
		$id = ($this->uri->segment(2,0));
		$data_karyawan_jabatan = $this->M_gl_karyawan_jabatan->get_karyawan_jabatan('id_kj',$id);
		if(!empty($data_karyawan_jabatan))
		{
			$data_karyawan_jabatan = $data_karyawan_jabatan->row();
			
			//HAPUS TRAINING
				$this->M_gl_karyawan_jabatan->hapus('id_kj',$id);
			//HAPUS TRAINING
			
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data perubahan jabatan (promosi,demosi,rotasi dan probation) '.$data_karyawan_jabatan->no_surat.' | '.$data_karyawan_jabatan->periode,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-promosi');
	}
	
	function list_karyawan()
	{
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (COALESCE(A.no_karyawan,'') LIKE '%".$_POST['cari']."%' OR COALESCE(A.nama_karyawan,'') LIKE '%".$_POST['cari']."%')
							";
		}
		else
		{
			$cari = "AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
		}
		
		
		$list_karyawan = $this->M_gl_karyawan_jabatan->list_karyawan_belum_terdaftar_promo($cari,$_POST['limit'],$_POST['offset']);
		
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
								<button type="button" onclick="insert('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button>
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
	
	function cek_karyawan_jabatan()
	{
		$hasil_cek = $this->M_gl_karyawan_jabatan->get_karyawan_jabatan('kode_promosi',$_POST['kode_promosi']);
		echo $hasil_cek;
	}
	
	function ubah_proses_karyawan_promo()
	{
		$hasil_cek = $this->M_gl_karyawan_jabatan->edit_ubah_nilai($this->session->userdata('ses_kode_kantor'),$_POST['id_kj'],$_POST['nilai'],$_POST['status'],$_POST['ket_nilai']);
		
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