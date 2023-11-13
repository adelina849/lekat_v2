<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_hpp_jasa extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_hpp_jasa','M_gl_produk','M_gl_satuan'));
		
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
				$id_produk = $this->uri->segment(2,0);
				$data_produk = $this->M_gl_produk->get_produk('MD5(id_produk)',$id_produk);
				if(!empty($data_produk))
				{
					$data_produk = $data_produk->row();
					
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_produk) = '".$id_produk."'
						AND (
								COALESCE(B.kode_produk,'') = '".str_replace("'","",$_GET['cari'])."'
								OR COALESCE(B.nama_produk,'') = '".str_replace("'","",$_GET['cari'])."'
								OR COALESCE(C.kode_assets,'') = '".str_replace("'","",$_GET['cari'])."'
								OR COALESCE(C.nama_assets,'') = '".str_replace("'","",$_GET['cari'])."'
							)";
					}
					else
					{
						$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_produk) = '".$id_produk."'
						";
					}
					
					
					$order_by = " ORDER BY A.tgl_ins DESC";
					
					$jum_row = $this->M_gl_hpp_jasa->count_hpp_jasa_limit($cari)->JUMLAH;
					
					$this->load->library('pagination');
					//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
					//$config['base_url'] = base_url().'admin/jabatan/';
					
					$config['first_url'] = site_url('gl-admin-produk-jasa-hpp-jasa/'.$id_produk.'?'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-admin-produk-jasa-hpp-jasa/'.$id_produk);
					$config['total_rows'] = $jum_row;
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
					
					$list_hpp_jasa = $this->M_gl_hpp_jasa->list_hpp_jasa_limit($cari,$order_by,$config['per_page'],$this->uri->segment(3,0));
					
					$list_satuan = $this->M_gl_satuan->list_satuan_limit(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'",1000,0);
					
					$msgbox_title = " HPP Jasa/Tindakan";
					
					
					if($config['per_page'] > $jum_row)
					{
						$jum_row_tampil = $jum_row;
					}
					else
					{
						$jum_row_tampil = $config['per_page'];
					}
					
					$offset = (integer) $this->uri->segment(3,0);
					$max_data = $offset + $jum_row_tampil;
					$offset = $offset + 1;
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$sum_pesan = "Menampilkan ".$jum_row_tampil." Dari ".$jum_row." Data Pencarian ".str_replace("'","",$_GET['cari'])." dimulai dari data ke ".$offset." Sampai ".$max_data;
					}
					else
					{
						$sum_pesan = "Menampilkan ".$jum_row_tampil." Dari ".$jum_row." Data dimulai dari data ke ".$offset." Sampai ".$max_data;
					}
					
					$data = array('page_content'=>'gl_admin_hpp_jasa','halaman'=>$halaman,'list_hpp_jasa'=>$list_hpp_jasa,'msgbox_title' => $msgbox_title,'sum_pesan' => $sum_pesan,'data_produk'=>$data_produk,'list_satuan'=>$list_satuan);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-produk-jasa');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_produk()
	{	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "
					WHERE B.id_hpp_jasa IS NULL
					AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					-- AND A.isProduk IN ('PRODUK','CONSUMABLE')
					AND A.isProduk = 'CONSUMABLE'
					AND (A.kode_produk LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_produk LIKE '%".str_replace("'","",$_POST['cari'])."%')";
		}
		else
		{
			$cari = " 
						WHERE B.id_hpp_jasa IS NULL
						AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
						-- AND A.isProduk IN ('PRODUK','CONSUMABLE')
						AND A.isProduk = 'CONSUMABLE'
					";
		}
		
		$order_by = " ORDER BY A.nama_produk ASC ";
		
		$list_produk = $this->M_gl_hpp_jasa->list_produk($_POST['id_produk'],$cari,$order_by,$_POST['limit'],$_POST['offset']);
		//($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_produk))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							//echo '<th width="10%">FOTO</th>';
							echo '<th width="65%">DATA PRODUK</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_produk->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_produk-'.$no.'">';
					
						echo'<td>'.$no.'</td>';
						echo'<td>
								<b>KODE : </b>'.$row->kode_produk.' 
								<br/> <b>NAMA : </b>'.$row->nama_produk.' 
								<br/> <b>PRODUKSI : </b>'.$row->produksi_oleh.'
							</td>';
						echo'<td>
								<!-- <button type="button" onclick="insert_produk('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button> -->
								<button type="button" onclick="insert_produk('.$no.')" class="btn btn-primary btn-sm"  data-dismiss="modal" >Pilih</button>
							</td>';
							
						echo'<input type="hidden" id="get_tr_3_'.$no.'" name="get_tr_3_'.$no.'" value="tr_list_produk-'.$no.'" />';
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
	
	function list_assets()
	{	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "
					WHERE B.id_hpp_jasa IS NULL
					AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.isUang = 0
					AND (A.kode_assets LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_assets LIKE '%".str_replace("'","",$_POST['cari'])."%')";
		}
		else
		{
			$cari = " 
						WHERE B.id_hpp_jasa IS NULL
						AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
						AND A.isUang = 0
					";
		}
		
		$order_by = " ORDER BY A.nama_assets ASC ";
		
		$list_assets = $this->M_gl_hpp_jasa->list_assets($_POST['id_produk'],$cari,$order_by,$_POST['limit'],$_POST['offset']);
		//($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_assets))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							//echo '<th width="10%">FOTO</th>';
							echo '<th width="65%">DATA ASSETS</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_assets->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_produk-'.$no.'">';
					
						echo'<td>'.$no.'</td>';
						echo'<td>
								<b>KODE : </b>'.$row->kode_assets.' 
								<br/> <b>NAMA : </b>'.$row->nama_assets.' 
								<br/> <b>PIC : </b>'.$row->pemegang.'
							</td>';
						echo'<td>
								<!-- <button type="button" onclick="insert_produk('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button> -->
								<button type="button" onclick="insert_assets('.$no.')" class="btn btn-primary btn-sm"  data-dismiss="modal" >Pilih</button>
							</td>';
							
						echo'<input type="hidden" id="get_tr_4_'.$no.'" name="get_tr_4_'.$no.'" value="tr_list_produk-'.$no.'" />';
						echo'<input type="hidden" id="id_d_assets_4_'.$no.'" name="id_d_assets_4_'.$no.'" value="'.$row->id_d_assets.'" />';
						echo'<input type="hidden" id="kode_assets_4_'.$no.'" name="kode_assets_4_'.$no.'" value="'.$row->kode_assets.'" />';
						echo'<input type="hidden" id="nama_assets_4_'.$no.'" name="nama_assets_4_'.$no.'" value="'.$row->nama_assets.'" />';
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
					$data_produk = $this->M_gl_produk->get_produk('id_produk',$_POST['id_produk']);
					if(!empty($data_produk))
					{
						$data_produk = $data_produk->row();
						
						$this->M_gl_hpp_jasa->edit
						(
							$_POST['stat_edit'], //$id_hpp_jasa,
							$_POST['id_produk'],
							$_POST['id_produk_hpp'],
							$_POST['id_d_assets'], //id_assets
							$_POST['banyaknya'],
							$_POST['satuan'],
							$_POST['harga'],
							$_POST['ket_hpp'],
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
								'Melakukan perubahan HPP Produk '.$data_produk->kode_produk.' | '.$data_produk->nama_produk,
								$this->M_gl_pengaturan->getUserIpAddr(),
								gethostname(),
								$this->session->userdata('ses_kode_kantor')
							);
						}
						/* CATAT AKTIFITAS EDIT*/
					}
				}
				else
				{
					$this->M_gl_hpp_jasa->simpan
					(
						$_POST['id_produk'],
						$_POST['id_produk_hpp'],
						$_POST['id_d_assets'], //id_assets
						$_POST['banyaknya'],
						$_POST['satuan'],
						$_POST['harga'],
						$_POST['ket_hpp'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					
					);
				}
				
				//UBAH HPP PRODUK
					$this->M_gl_hpp_jasa->ubah_hpp_jasa($_POST['id_produk']); //GAK USAH DI UPDATE DISINI
				//UBAH HPP PRODUK
				header('Location: '.base_url().'gl-admin-produk-jasa-hpp-jasa/'.MD5($_POST['id_produk']));
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function hapus()
	{
		$id_produk = ($this->uri->segment(2,0));
		$id_hpp_jasa = ($this->uri->segment(3,0));
		
		$data_produk_hpp = $this->M_gl_hpp_jasa->get_hpp_jasa('MD5(id_hpp_jasa)',$id_hpp_jasa);
		if(!empty($data_produk_hpp))
		{
			$data_produk_hpp = $data_produk_hpp->row();
			$this->M_gl_hpp_jasa->hapus('MD5(id_hpp_jasa)',$id_hpp_jasa);
			
			//UBAH HPP PRODUK
				$this->M_gl_hpp_jasa->ubah_hpp_jasa($data_produk_hpp->id_produk);
			//UBAH HPP PRODUK
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data HPP Produk '.$data_produk_hpp->id_produk,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-produk-jasa-hpp-jasa/'.$id_produk);
	}
	
	/*
	function cek_satuan()
	{
		$hasil_cek = $this->M_gl_satuan->get_satuan('kode_satuan',$_POST['kode_satuan']);
		echo $hasil_cek;
	}
	*/
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */