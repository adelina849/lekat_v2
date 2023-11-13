<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_lap_request_cabang extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_lap_pembelian','M_gl_costumer','M_gl_h_penjualan','M_gl_media_transaksi'));
		
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
				if((!empty($_GET['dari'])) && ($_GET['dari']!= "")  )
				{
					$dari = $_GET['dari'];
					$sampai = $_GET['sampai'];
				}
				else
				{
					$dari = date("Y-m-d");
					$sampai = date("Y-m-d");
				}
				
				if((!empty($_GET['cari_belum'])) && ($_GET['cari_belum']!= "")  )
				{
					$cari_belum = " AND F.no_faktur IS NULL";
				}
				else
				{
					$cari_belum = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							
								WHERE
								COALESCE(B.sts_pembelian, '') = 'SELESAI' 
								AND D.kode_supplier = '".$this->session->userdata('ses_kode_kantor')."' 
								AND D.type_supplier = 'CABANG' 
								AND DATE(B.tgl_h_pembelian) BETWEEN '".$dari."' AND '".$sampai."' 
								AND 
								(
									COALESCE(B.no_h_pembelian,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(D.kode_supplier, '') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(D.nama_supplier, '') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(E.nama_kantor, '') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
								
								".$cari_belum."
								";
					$cari_sum =
								"
							
								WHERE
								COALESCE(B.sts_pembelian, '') = 'SELESAI' 
								AND D.kode_supplier = '".$this->session->userdata('ses_kode_kantor')."' 
								AND D.type_supplier = 'CABANG' 
								AND DATE(B.tgl_h_pembelian) BETWEEN '".$dari."' AND '".$sampai."' 
								AND 
								(
									COALESCE(B.no_h_pembelian,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(D.kode_supplier, '') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(D.nama_supplier, '') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(E.nama_kantor, '') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
								";					
				}
				else
				{
					$cari = "
								WHERE
								COALESCE(B.sts_pembelian, '') = 'SELESAI' 
								AND D.kode_supplier = '".$this->session->userdata('ses_kode_kantor')."' 
								AND D.type_supplier = 'CABANG' 
								AND DATE(B.tgl_h_pembelian) BETWEEN '".$dari."' AND '".$sampai."' 
								".$cari_belum."
							";
							
					$cari_sum = "
								WHERE
								COALESCE(B.sts_pembelian, '') = 'SELESAI' 
								AND D.kode_supplier = '".$this->session->userdata('ses_kode_kantor')."' 
								AND D.type_supplier = 'CABANG' 
								AND DATE(B.tgl_h_pembelian) BETWEEN '".$dari."' AND '".$sampai."'
							";
				}
				$order_by = "ORDER BY A.id_h_pembelian DESC,A.tgl_ins DESC";
				
				
				//UNTUK AKUMULASI INFO
					$jum_row = $this->M_gl_lap_pembelian->count_pemesanan_dari_cabang_lain($cari_sum)->JUMLAH;
				//UNTUK AKUMULASI INFO
				
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				//$config['first_url'] = site_url('gl-admin-laporan-transaksi?'.http_build_query($_GET));
				$config['first_url'] = site_url('gl-admin-laporan-permintaan-cabang?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-laporan-permintaan-cabang/');
				$config['total_rows'] = $jum_row;
				$config['uri_segment'] = 2;	
				//$config['uri_segment'] = $_GET['var_offset'];	
				$config['per_page'] = 150;
				//$config['per_page'] = 10000;
				$config['num_links'] = 2;
				//$config['num_links'] = 20;
				//$config['suffix'] = '?' . http_build_query($_GET, '', "&");
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
				
				$list_pemesanan_dari_cabang_lain = $this->M_gl_lap_pembelian->list_pemesanan_dari_cabang_lain($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				
				//UNTUK AKUMULASI INFO
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
				//UNTUK AKUMULASI INFO
				
				
				//SUMMARY
					//$sum_laporan_h_pembelian = $this->M_gl_lap_pembelian->sum_lap_h_pembelian($cari);
					//$sum_laporan_h_penjualan = $sum_laporan_h_penjualan->row();
				//SUMMARY
				
				
				$msgbox_title = " Laporan Permintaan Produk Dari Cabang ";
				
				$data = array('page_content'=>'gl_admin_lap_req_cabang','halaman'=>$halaman, 'sum_pesan' => $sum_pesan,'msgbox_title' => $msgbox_title,'list_pemesanan_dari_cabang_lain' => $list_pemesanan_dari_cabang_lain);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function proses_permintaan_cabang()
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
				$id_h_pembelian = $this->uri->segment(2,0);
				$kode_cabang_pemesan = $this->uri->segment(3,0);
				$cari_request = "
						WHERE
						COALESCE(B.sts_pembelian, '') = 'SELESAI' 
						AND D.kode_supplier = '".$this->session->userdata('ses_kode_kantor')."' 
						AND D.type_supplier = 'CABANG'
						AND COALESCE(MD5(B.id_h_pembelian),'') = '".$id_h_pembelian."' 
							
						";
				$order_by = "ORDER BY A.tgl_ins DESC";
								
				$get_data_pembelian = $this->M_gl_lap_pembelian->list_pemesanan_dari_cabang_lain($cari_request,$order_by,1,0);
				if(!empty($get_data_pembelian))
				{
					$get_data_pembelian = $get_data_pembelian->row();
					
					$cari_data_costumer = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.no_costumer = '".$get_data_pembelian->kode_kantor_pemesan."' ";
					$get_data_costumer = $this->M_gl_costumer->get_costumer_with_kategori($cari_data_costumer);
					if(!empty($get_data_costumer))
					{
						$get_data_costumer = $get_data_costumer->row();
						
						$get_id_h_penjualan = $this->M_gl_h_penjualan->get_id_h_penjualan($this->session->userdata('ses_kode_kantor'));
						if(!empty($get_id_h_penjualan))
						{
							$id_h_penjualan = $get_id_h_penjualan->id_h_penjualan;
							
							//GET MDIA TRANSAKSI
							$cari_media = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND isDefault = '1'";
							$get_media_transaksi = $this->M_gl_media_transaksi->list_media_transaksi_limit($cari_media,"",1,0);
							
							if(!empty($get_media_transaksi))
							{
								$get_media_transaksi = $get_media_transaksi->row();
								$get_media_transaksi = $get_media_transaksi->nama_media;
							}
							else
							{
								$get_media_transaksi = "";
							}
							
							
							$this->M_gl_h_penjualan->simpan_h_penjualan_awal
							(
								$id_h_penjualan,
								$get_data_costumer->id_costumer,
								$this->session->userdata('ses_id_karyawan'), //$id_karyawan,
								'', //$id_dokter,
								'', //$id_h_diskon,
								$id_h_penjualan, //$no_faktur,
								0, //$no_antrian,
								$get_data_costumer->no_costumer,
								$get_data_costumer->nama_lengkap,
								$get_data_costumer->nama_kat_costumer,
								$get_data_costumer->id_kat_costumer,
								$get_data_costumer->kode_kantor,
								date("Y-m-d"), //$tgl_h_penjualan,
								'CASH' , //$status_penjualan,
								$get_data_pembelian->no_h_pembelian, //$ket_penjualan,
								'PENJUALAN LANGSUNG', //$type_h_penjualan,
								$this->session->userdata('ses_id_karyawan'), //$user_ins,
								$this->session->userdata('ses_kode_kantor'), //$kode_kantor,
								'PENDING', //$sts_penjualan,
								$get_media_transaksi
								
							);
							
							//DIGANTI INI KAN INSERT NTO LANGSUNG, DIGANTI JADI PROCEDURE LOPING
								//$this->M_gl_h_penjualan->simpan_penjualan_dari_req($this->session->userdata('ses_kode_kantor'),$id_h_penjualan,$id_h_pembelian,$kode_cabang_pemesan);
							//DIGANTI INI KAN INSERT NTO LANGSUNG, DIGANTI JADI PROCEDURE LOPING
							$this->M_gl_h_penjualan->simpan_penjualan_dari_req_by_procedure($this->session->userdata('ses_kode_kantor'),$id_h_penjualan,$get_data_pembelian->id_h_pembelian,$get_data_pembelian->kode_kantor_pemesan);
							
							
							header('Location: '.base_url().'gl-admin-pemeriksaan-dokter-proses/'.md5($id_h_penjualan));
						}
						else
						{
							//echo 'ID PENJUALAN KOSONG';
							echo 'Ada Masalah dengan data penjualan ! SIlahkan cek data penjualan proses kembali.';
							//header('Location: '.base_url().'gl-admin-laporan-permintaan-cabang');
						}
					}
					else
					{
						//echo 'DATA COSTUMER COSTUMER KOSONG';
						echo 'Ada Masalah dengan data costumer, silahkan periksa kembali data costumer nya dan ulangi !';
						//header('Location: '.base_url().'gl-admin-laporan-permintaan-cabang');
					}
				}
				else
				{
					//echo 'DATA PEMBELIAN PEMBELIAN KOSONG';
					echo'Ada masalah dengan order/PO/pembelian, silahkan periksa dan ulangi kembali';
					//header('Location: '.base_url().'gl-admin-laporan-permintaan-cabang');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_lap_h_pembelian.php */