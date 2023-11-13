<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_harga_pasien extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_harga_pasien','M_gl_pst_satuan','M_gl_satuan','M_gl_kat_costumer'));
		
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
					$cari = str_replace("'","",$_GET['cari']);
				}
				else
				{
					$cari = "";
				}
				
				$jum_row = $this->M_gl_harga_pasien->count_harga_pasien($cari)->JUMLAH;
				
				$this->load->library('pagination');
				
				$config['first_url'] = site_url('gl-admin-harga-pasien?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-harga-pasien/');
				$config['total_rows'] = $jum_row;
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
				
				$list_harga_pasien = $this->M_gl_harga_pasien->list_harga_pasien($this->session->userdata('ses_kode_kantor'),$cari,$this->uri->segment(2,0),$config['per_page']);
				
				if(!empty($list_harga_pasien))
				{
					$list_field = $list_harga_pasien->field_data();
				}
				else
				{
					$list_field = false;
				}
				
				
				//($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$msgbox_title = " Harga diberikan kepada pelanggan ";
				
				if($config['per_page'] > $jum_row)
				{
					$jum_row_tampil = $jum_row;
				}
				else
				{
					$jum_row_tampil = $config['per_page'];
				}
				
				$offset = (integer) $this->uri->segment(2,0);
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
				
				$data = array('page_content'=>'gl_admin_harga_pasien','halaman'=>$halaman,'list_harga_pasien'=>$list_harga_pasien,'msgbox_title' => $msgbox_title,'list_field' => $list_field, 'sum_pesan' => $sum_pesan);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function simpan_old()
	{
		$id_produk = $_POST['id_produk'];
		
		$id_satuan = $this->M_gl_satuan->get_satuan('kode_satuan',$_POST['id_satuan']);
		$id_satuan = $id_satuan->row()->id_satuan;
		$harga_jual = str_replace(",","",$_POST['harga_jual']); //$_POST['harga_jual'];
		
		if($harga_jual > 0)
		{
			$cek_satuan_konveri = $this->M_gl_satuan_konversi->cek_satuan_konversi(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$id_produk."' AND id_satuan = '".$id_satuan."'");
			
			if(!empty($cek_satuan_konveri))
			{
				$cek_satuan_konveri = $cek_satuan_konveri->row();
				
				
				$this->M_gl_satuan_konversi->edit(
													$cek_satuan_konveri->id_satuan_konversi,
													$cek_satuan_konveri->besar_konversi,
													$harga_jual,
													$this->session->userdata('ses_id_karyawan'), //$user_updt,
													$this->session->userdata('ses_kode_kantor') //$kode_kantor
												);
			}
		}
	}
	
	
	function simpan()
	{
		$id_produk = $_POST['id_produk'];
		
		$id_satuan = $this->M_gl_satuan->get_satuan('kode_satuan',$_POST['id_satuan']);
		$id_satuan = $id_satuan->row()->id_satuan;
		
		$id_kat_costumer = $this->M_gl_kat_costumer->get_kat_costumer('nama_kat_costumer',$_POST['strKatCos']);
		$id_kat_costumer = $id_kat_costumer->row()->id_kat_costumer;
		
		$harga = $_POST['harga'];
		$media = $_POST['strMedia'];
		
		if($harga > 0)
		{
			$cek_harga_satuan_costumer = $this->M_gl_harga_pasien->cek_harga_satuan_costumer("  WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$id_produk."' AND id_satuan = '".$id_satuan."' AND id_costumer = '".$id_kat_costumer."' AND media = '".$media."' ");
			
			if(!empty($cek_harga_satuan_costumer))
			{
				$cek_harga_satuan_costumer = $cek_harga_satuan_costumer->row();
				
				if($this->session->userdata('ses_kode_kantor') == 'PST')
				{
					$this->M_gl_harga_pasien->edit_pusat(
													$cek_harga_satuan_costumer->id_phsc,
													$harga,
													$media,
													$this->session->userdata('ses_id_karyawan')
												);
				}
				else
				{
					$this->M_gl_harga_pasien->edit(
													$cek_harga_satuan_costumer->id_phsc,
													$harga,
													$media,
													$this->session->userdata('ses_id_karyawan'), //$user_updt,
													$this->session->userdata('ses_kode_kantor') //$kode_kantor
												);
				}
			}
			else
			{
				if($this->session->userdata('ses_kode_kantor') == 'PST')
				{
					$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
					$get_id_phsc = $this->M_gl_harga_pasien->get_id_phsc();
					
					if(!empty($list_kantor))
					{
						$list_result = $list_kantor->result();
						
						foreach($list_result as $row)
						{
							$this->M_gl_harga_pasien->simpan_pusat(
								$get_id_phsc->id_phsc,
								$id_produk,
								$id_satuan,
								$id_kat_costumer, //$id_costumer,
								$harga,
								'0', //$besar_prsn,
								'*', //$optr_prsn,
								'', //$ket,
								$media,
								$this->session->userdata('ses_id_karyawan'), //$user_updt,
								$row->kode_kantor
							);
						}
					}
				}
				else
				{
					$this->M_gl_harga_pasien->simpan(
						$id_produk,
						$id_satuan,
						$id_kat_costumer, //$id_costumer,
						$harga,
						'0', //$besar_prsn,
						'*', //$optr_prsn,
						'', //$ket,
						$media,
						$this->session->userdata('ses_id_karyawan'), //$user_updt,
						$this->session->userdata('ses_kode_kantor') //$kode_kantor
					);
				}
				
			}
		}
		else
		{
			if($this->session->userdata('ses_kode_kantor') == 'PST')
			{
				$this->M_gl_harga_pasien->hapus(" WHERE id_produk = '".$id_produk."' AND id_satuan = '".$id_satuan."' AND id_costumer = '".$id_kat_costumer."'");
			}
			else
			{
				$this->M_gl_harga_pasien->hapus(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$id_produk."' AND id_satuan = '".$id_satuan."' AND id_costumer = '".$id_kat_costumer."'");
			}
		}
		
		//echo 'BERHASIL';
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */