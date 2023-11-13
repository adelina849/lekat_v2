<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_d_diskon extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_h_diskon','M_gl_d_diskon','M_gl_produk','M_gl_satuan_konversi','M_gl_kat_produk'));
		
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
				$id_h_diskon = $this->uri->segment(2,0);
				$cek_h_diskon = $this->M_gl_h_diskon->get_h_diskon('id_h_diskon',$id_h_diskon);
				if(!empty($cek_h_diskon))
				{
					$cek_h_diskon = $cek_h_diskon->row();
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND A.id_h_diskon = '".$id_h_diskon."' AND (COALESCE(B.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' OR COALESCE(B.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' ) ";
					}
					else
					{
						$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_h_diskon = '".$id_h_diskon."'";
					}
					
					$order_by = " ORDER BY A.tgl_ins DESC ";
					
					$this->load->library('pagination');
					//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
					//$config['base_url'] = base_url().'admin/jabatan/';
					
					$config['first_url'] = site_url('gl-admin-produks-diskon-promo/'.$id_h_diskon.'?'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-admin-produks-diskon-promo/'.$id_h_diskon);
					$config['total_rows'] = $this->M_gl_d_diskon->count_d_diskon_limit($cari)->JUMLAH;
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
					
					$list_d_diskon = $this->M_gl_d_diskon->list_d_diskon_limit($cari,$order_by,$config['per_page'],$this->uri->segment(3,0));
					
					
					$cari_kat_produk = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
					$list_kat_produk = $this->M_gl_kat_produk->list_kat_produk_limit($cari_kat_produk,1000,0);
					
					$msgbox_title = " Pengaturan Produk untuk  ".$cek_h_diskon->nama_diskon;
					
					$data = array('page_content'=>'gl_admin_d_diskon','halaman'=>$halaman,'list_d_diskon'=>$list_d_diskon,'msgbox_title' => $msgbox_title,'list_kat_produk' => $list_kat_produk);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-diskon-promo');
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
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."' ");
			
			if(!empty($cek_ses_login))
			{
				$id_h_diskon = $this->uri->segment(2,0);
				$cek_h_diskon = $this->M_gl_h_diskon->get_h_diskon('id_h_diskon',$id_h_diskon);
				if(!empty($cek_h_diskon))
				{
					$cek_h_diskon = $cek_h_diskon->row();
					if (!empty($_POST['stat_edit']))
					{	
						$this->M_gl_d_diskon->edit
						(
						
							$_POST['stat_edit'],
							$_POST['id_h_diskon'],
							$_POST['id_produk'],
							
							$_POST['id_kat_produk'],
							'', //$_POST['kode_kat_produk'],
							$_POST['nama_kat_produk'],
							
							
							'', //$_POST['nama_d_diskon'],
							str_replace(",","",$_POST['nominal']) , //$_POST['nominal']
							$_POST['banyaknya'],
							$_POST['kode_satuan'],
							$_POST['status_konversi'],
							$_POST['konversi'],
							$_POST['ket_d_diskon'],
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
								'Melakukan perubahan data Produk Diskon Promo '.$cek_h_diskon->nama_diskon,
								$this->M_gl_pengaturan->getUserIpAddr(),
								gethostname(),
								$this->session->userdata('ses_kode_kantor')
							);
						}
						/* CATAT AKTIFITAS EDIT*/
					}
					else
					{
						$this->M_gl_d_diskon->simpan
						(
						
							$_POST['id_h_diskon'],
							$_POST['id_produk'],
							
							$_POST['id_kat_produk'],
							'', //$_POST['kode_kat_produk'],
							$_POST['nama_kat_produk'],
							
							'', //$_POST['nama_d_diskon'],
							str_replace(",","",$_POST['nominal']) , //$_POST['nominal']
							$_POST['banyaknya'],
							$_POST['kode_satuan'],
							$_POST['status_konversi'],
							$_POST['konversi'],
							$_POST['ket_d_diskon'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						
						);
					}
					header('Location: '.base_url().'gl-admin-produks-diskon-promo/'.$cek_h_diskon->id_h_diskon);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-diskon-promo');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function hapus()
	{
		$id_d_diskon = ($this->uri->segment(3,0));
		$data_d_diskon = $this->M_gl_d_diskon->list_d_diskon_limit(" WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_d_diskon = '".$id_d_diskon."'","",1,0);
		if(!empty($data_d_diskon))
		{
			$data_d_diskon = $data_d_diskon->row();
			$id_h_diskon = $this->uri->segment(2,0);
			$cek_h_diskon = $this->M_gl_h_diskon->get_h_diskon('id_h_diskon',$id_h_diskon);
			if(!empty($cek_h_diskon))
			{
				$cek_h_diskon = $cek_h_diskon->row();
			
				$this->M_gl_d_diskon->hapus('id_d_diskon',$id_d_diskon);
				
				/* CATAT AKTIFITAS HAPUS*/
				if($this->session->userdata('catat_log') == 'Y')
				{
					$this->M_gl_log->simpan_log
					(
						$this->session->userdata('ses_id_karyawan'),
						'DELETE',
						'Melakukan penghapusan data produk diskon promo | '.$cek_h_diskon->nama_diskon.' Dengan Produk '.$data_d_diskon->nama_produk,
						$this->M_gl_pengaturan->getUserIpAddr(),
						gethostname(),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				/* CATAT AKTIFITAS HAPUS*/
			}
		}
		
		header('Location: '.base_url().'gl-admin-produks-diskon-promo/'.$id_h_diskon);
	}
	
	function cek_d_diskon()
	{
		$hasil_cek = $this->M_gl_d_diskon->get_d_diskon('id_produk',$_POST['id_produk']);
		echo $hasil_cek;
	}
	
	function list_produk()
	{	
		$id_h_diskon = $_POST['id_h_diskon'];
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.isProduk IN ('PRODUK','JASA')
					AND (A.kode_produk LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_produk LIKE '%".str_replace("'","",$_POST['cari'])."%') AND D.id_d_diskon IS NULL";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.isProduk IN ('PRODUK','JASA') AND D.id_d_diskon IS NULL";
		}
		
		$order_by = " ORDER BY A.nama_produk ASC ";
		
		$list_produk = $this->M_gl_d_diskon->list_produk_d_diskon_limit($id_h_diskon,$cari,$order_by,$_POST['limit'],$_POST['offset']);
		//($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_produk))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="10%">FOTO</th>';
							echo '<th width="55%">DATA PRODUK</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_produk->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_'.$no.'">';
					
						echo'<td>'.$no.'</td>';
						
						
						if($row->img_file == "")
						{
							$src = base_url().'assets/global/no-image.jpg';
							echo '<td><img id="IMG_'.$no.'"  width="100px" height="100px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" />
							</td>';
						}
						else
						{
							//$src = base_url().'assets/global/karyawan/'.$row->avatar;
							$src = base_url().''.$row->img_url.''.$row->img_file;
							echo '<td><img id="IMG_'.$no.'"  width="100px" height="100px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" />
							</td>';
						}
						echo'<td>
								<b>KODE : </b>'.$row->kode_produk.' 
								<br/> <b>NAMA : </b>'.$row->nama_produk.' 
								<br/> <b>PRODUKSI : </b>'.$row->produksi_oleh.'
								<br/> <b>SPESIFIKASI : </b>'.$row->spek_produk.'
							</td>';
						echo'<td>
								<button type="button" onclick="insert_produk('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button>
							</td>';
						echo'<input type="hidden" id="id_produk_3_'.$no.'" name="id_produk_3_'.$no.'" value="'.$row->id_produk.'" />';
						echo'<input type="hidden" id="kode_produk_3_'.$no.'" name="kode_produk_3_'.$no.'" value="'.$row->kode_produk.'" />';
						echo'<input type="hidden" id="nama_produk_3_'.$no.'" name="nama_produk_3_'.$no.'" value="'.$row->nama_produk.'" />';
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
	
	public function tampilkan_satuan_produk()
	{
		$id_produk = $_POST['id_produk'];
		$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_produk = '".$id_produk."'";
		$order_by = " ORDER BY A.besar_konversi ASC,COALESCE(B.kode_satuan,'') ASC";
		$list_satuan = $this->M_gl_satuan_konversi->list_data_satuan_konversi($cari,$order_by,0,100);
		
		if (!empty($list_satuan))
		{
			$list_result = $list_satuan->result();
			foreach($list_result as $row)
			{
				echo '<option value="'.$row->kode_satuan.'-'.$row->status.'-'.$row->besar_konversi.'">'.$row->kode_satuan.'</option>';
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_promo_diskon.php */