<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_promo_diskon extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_h_diskon','M_gl_d_diskon','M_gl_kat_costumer','M_gl_produk','M_gl_satuan_konversi','M_gl_kat_produk'));
		
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
							AND (A.nama_diskon LIKE '%".str_replace("'","",$_GET['cari'])."%' OR COALESCE(B.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' ) ";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-diskon-promo?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-diskon-promo/');
				$config['total_rows'] = $this->M_gl_h_diskon->count_h_diskon_limit($cari)->JUMLAH;
				//$config['total_rows'] = 30;
				$config['uri_segment'] = 2;	
				$config['per_page'] = 10000;
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
				
				$list_h_diskon = $this->M_gl_h_diskon->list_h_diskon_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$list_kat_costumer = $this->M_gl_kat_costumer->list_kat_costumer_limit(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'",100,0);
				
				$cari_kat_kategori_produk = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				$list_kat_produk = $this->M_gl_kat_produk->list_kat_produk_limit($cari_kat_kategori_produk,1000,0);
				
				$msgbox_title = " Pengaturan Diskon dan Promo ";
				
				$data = array('page_content'=>'gl_admin_h_diskon','halaman'=>$halaman,'list_h_diskon'=>$list_h_diskon,'msgbox_title' => $msgbox_title, 'list_kat_costumer' => $list_kat_costumer,'list_kat_produk' => $list_kat_produk);
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
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."' ");
			
			if(!empty($cek_ses_login))
			{
				if ( (!empty($_POST['id_kat_produk'])) AND ($_POST['id_kat_produk'] != "") )
				{
					$arr_kat_produk  = $_POST['id_kat_produk'];
					$pieces = explode("|", $arr_kat_produk);
					$id_kat_produk = $pieces[0]; // piece1
					$kode_kat_produk = $pieces[1]; // piece1
					$nama_kat_produk = $pieces[2]; // piece1
				}
				else
				{
					$id_kat_produk = "";
					$kode_kat_produk = "";
					$nama_kat_produk = "";
				}
				
				
				
				if (!empty($_POST['stat_edit']))
				{	
					$this->M_gl_h_diskon->edit
					(
						$_POST['stat_edit'],
						$_POST['id_kat_costumer'],
						$_POST['id'],
						'produks', //$_POST['group_by'],
						$_POST['nama_diskon'],
						str_replace(",","",$_POST['besar_diskon']) , //$_POST['besar_diskon'],
						$_POST['optr_diskon'],
						$_POST['ket_diskon'],
						$_POST['dari'],
						$_POST['sampai'],
						$_POST['satuan'],
						$_POST['satuan_diskon'],
						$_POST['optr_kondisi'],
						str_replace(",","",$_POST['besar_pembelian']) , //$_POST['besar_pembelian'],
						'0', //$_POST['set_default'],
						
						$_POST['isAkumulasi'],
						$id_kat_produk,
						$kode_kat_produk,
						$nama_kat_produk,
						
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
							'Melakukan perubahan data Diskon Promo '.$_POST['nama_diskon'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					$this->M_gl_h_diskon->simpan
					(
						$_POST['id_kat_costumer'],
						$_POST['id'],
						'produks', //$_POST['group_by'],
						$_POST['nama_diskon'],
						str_replace(",","",$_POST['besar_diskon']) , //$_POST['besar_diskon'],
						$_POST['optr_diskon'],
						$_POST['ket_diskon'],
						$_POST['dari'],
						$_POST['sampai'],
						$_POST['satuan'],
						$_POST['satuan_diskon'],
						$_POST['optr_kondisi'],
						str_replace(",","",$_POST['besar_pembelian']) , //$_POST['besar_pembelian'],
						'0', //$_POST['set_default'],
						
						$_POST['isAkumulasi'],
						$id_kat_produk,
						$kode_kat_produk,
						$nama_kat_produk,
						
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					
					);
				}
				
				/*SET HARI DISKON*/
					$this->M_gl_pengaturan->update_set_0_h_diskon($data_login->kode_kantor);
					$this->M_gl_pengaturan->update_set_1_h_diskon($data_login->kode_kantor);
				/*SET HARI DISKON*/
				header('Location: '.base_url().'gl-admin-diskon-promo');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function hapus()
	{
		$id_h_diskon = ($this->uri->segment(2,0));
		$data_h_diskon = $this->M_gl_h_diskon->get_h_diskon('id_h_diskon',$id_h_diskon);
		if(!empty($data_h_diskon))
		{
			$data_h_diskon = $data_h_diskon->row();
			$this->M_gl_h_diskon->hapus('id_h_diskon',$id_h_diskon);
			$this->M_gl_d_diskon->hapus('id_h_diskon',$id_h_diskon);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data diskon promo | '.$data_h_diskon->nama_diskon,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-diskon-promo');
	}
	
	function cek_h_diskon()
	{
		$hasil_cek = $this->M_gl_h_diskon->get_h_diskon('nama_diskon',$_POST['nama_diskon']);
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			echo $hasil_cek->nama_h_diskon;
		}
		else
		{
			return false;
		}
	}
	
	function list_produk()
	{	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.isProduk IN ('PRODUK','JASA')
					AND (A.kode_produk LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_produk LIKE '%".str_replace("'","",$_POST['cari'])."%')";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.isProduk IN ('PRODUK','JASA')";
		}
		
		$order_by = " ORDER BY A.nama_produk ASC ";
		
		$list_produk = $this->M_gl_produk->list_produk_limit($cari,$order_by,$_POST['limit'],$_POST['offset']);
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
		$order_by = " ORDER BY COALESCE(B.kode_satuan,'') ASC";
		$list_satuan = $this->M_gl_satuan_konversi->list_data_satuan_konversi($cari,$order_by,0,100);
		
		if (!empty($list_satuan))
		{
			$list_result = $list_satuan->result();
			foreach($list_result as $row)
			{
				echo '<option value="'.$row->kode_satuan.'">'.$row->kode_satuan.'</option>';
			}
		}
	}
	
	function list_hari_diskon()
	{	
		$list_hari_diskon = $this->M_gl_h_diskon->list_hari_diskon($_POST['id_h_diskon'],$this->session->userdata('ses_kode_kantor'));
		//($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_hari_diskon))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="75%">NAMA HARI</th>';
							echo '<th width="20%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_hari_diskon->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_'.$no.'">';
					
						echo'<td>'.$no.'</td>';
						echo'<td>'.$row->hari.'</td>';
						
						if($row->AKTIF == '1')
						{
							echo'<td>
								<div class="form-group">
									<label>
										<input type="checkbox" id="isAktif_'.$no.'" name="isAktif_'.$no.'" class="flat-red" onclick="cek_aktif('.$no.')" checked>
									</label>
								</div>
							</td>';
						}
						else
						{
							echo'<td>
								<div class="form-group">
									<label>
										<input type="checkbox"  id="isAktif_'.$no.'" name="isAktif_'.$no.'" class="flat-red" onclick="cek_aktif('.$no.')">
									</label>
								</div>
							</td>';
						}
						
						echo'<input type="hidden" id="hari_3_'.$no.'" name="hari_3_'.$no.'" value="'.$row->hari.'" />';
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
	
	function cek_isNotActiveHari()
	{
		$cari = " WHERE id_h_diskon = '".$_POST['id_h_diskon']."' AND hari = '".$_POST['hari']."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."';";
		$cek_hari = $this->M_gl_h_diskon->cek_hari_diskon($cari);
		if(!empty($cek_hari))
		{
			$cek_hari = $cek_hari->row();
			$this->M_gl_h_diskon->hapus_hari($_POST['id_h_diskon'],$_POST['hari'],$this->session->userdata('ses_kode_kantor'));
			
			$cari2 = " WHERE id_h_diskon = '".$_POST['id_h_diskon']."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."';";
			$cek_hari2 = $this->M_gl_h_diskon->cek_hari_diskon($cari2);
			$hasil_hari = "";
			if(!empty($cek_hari2))
			{
				$list_result = $cek_hari2->result();
				foreach($list_result as $row)
				{
					$hasil_hari = $hasil_hari.','.$row->hari;
				}
			}
			
			$hasil_hari = "Aktif pada hari : ".$hasil_hari;
		}
		else
		{
			$this->M_gl_h_diskon->simpan_hari($_POST['id_h_diskon'],$_POST['hari'],$this->session->userdata('ses_kode_kantor'));
			
			
			$cari2 = " WHERE id_h_diskon = '".$_POST['id_h_diskon']."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."';";
			$cek_hari2 = $this->M_gl_h_diskon->cek_hari_diskon($cari2);
			$hasil_hari = "";
			if(!empty($cek_hari2))
			{
				$list_result = $cek_hari2->result();
				foreach($list_result as $row)
				{
					$hasil_hari = $hasil_hari.','.$row->hari;
				}
			}
			
			$hasil_hari = "Aktif pada hari : ".$hasil_hari;
		}
		
		/*SET HARI DISKON*/
			$this->M_gl_pengaturan->update_set_0_h_diskon($this->session->userdata('ses_kode_kantor'));
			$this->M_gl_pengaturan->update_set_1_h_diskon($this->session->userdata('ses_kode_kantor'));
		/*SET HARI DISKON*/
		echo $hasil_hari;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_promo_diskon.php */