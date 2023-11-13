<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_pst_admin_lap_h_pembelian extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_pst_lap_pembelian','M_gl_pengaturan'));
		
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

				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				} else {
					$kode_kantor = '';
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE A.kode_kantor = '".$kode_kantor."' 
							AND A.sts_pembelian = 'SELESAI'
							AND DATE(A.tgl_h_pembelian) BETWEEN '".$dari."' AND '".$sampai."'
							AND 
							(
								A.no_h_pembelian LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(D.kode_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(D.nama_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$kode_kantor."' 
							AND A.sts_pembelian = 'SELESAI'
							AND DATE(A.tgl_h_pembelian) BETWEEN '".$dari."' AND '".$sampai."' ";
				}
				$order_by = "ORDER BY A.tgl_ins DESC";
				
				
				//UNTUK AKUMULASI INFO
					$jum_row = $this->M_gl_pst_lap_pembelian->count_lap_h_pembelian_limit($cari)->JUMLAH;
				//UNTUK AKUMULASI INFO
				
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				//$config['first_url'] = site_url('gl-admin-laporan-transaksi?'.http_build_query($_GET));
				$config['first_url'] = site_url('gl-pusat-admin-laporan-pembelian?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-pusat-admin-laporan-pembelian/');
				$config['total_rows'] = $jum_row;
				$config['uri_segment'] = 2;	
				//$config['uri_segment'] = $_GET['var_offset'];	
				$config['per_page'] = 30;
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
				
				$list_laporan_h_pembelian = $this->M_gl_pst_lap_pembelian->list_lap_h_pembelian($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				
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
					$sum_laporan_h_pembelian = $this->M_gl_pst_lap_pembelian->sum_lap_h_pembelian($cari);
					//$sum_laporan_h_penjualan = $sum_laporan_h_penjualan->row();
				//SUMMARY
				
				
				$msgbox_title = " Laporan Pembelian/Purchase Order Ke Supplier ";
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("")->result();
				
				$data = array(
						'page_content'=>'gl_pusat_admin_lap_h_pembelian',
						'halaman'=>$halaman, 
						'sum_pesan' => $sum_pesan,
						'msgbox_title' => $msgbox_title,
						'list_laporan_h_pembelian' => $list_laporan_h_pembelian,
						'sum_laporan_h_pembelian' => $sum_laporan_h_pembelian,
						'list_kantor' => $list_kantor,
					);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_ajax_detail_pembelian()
	{	
		$kode_kantor = $this->input->post('kode_kantor');

		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "
					WHERE A.kode_kantor = '".$kode_kantor."' 
					AND A.id_h_pembelian = '".str_replace("'","",$_POST['id_h_pembelian'])."'
					AND COALESCE(B.sts_pembelian,'') = 'SELESAI'
					AND (
							A.kode_produk LIKE '%".str_replace("'","",$_POST['cari'])."%' 
							OR A.nama_produk LIKE '%".str_replace("'","",$_POST['cari'])."%'
							OR COALESCE(B.no_h_pembelian,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						)";
		}
		else
		{
			$cari = " 
						WHERE A.kode_kantor = '".$kode_kantor."' 
						AND A.id_h_pembelian = '".str_replace("'","",$_POST['id_h_pembelian'])."'
						AND COALESCE(B.sts_pembelian,'') = 'SELESAI'
					";
		}
		
		$order_by = " ORDER BY COALESCE(B.tgl_h_pembelian,'') DESC, A.tgl_ins DESC ";
		
		$list_d_pembelian = $this->M_gl_pst_lap_pembelian->list_lap_d_pembelian_with_penerimaan($cari,$order_by,$_POST['limit'],$_POST['offset']);
		//($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_d_pembelian))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="40%">PRODUK</th>';
							echo '<th width="15%">JUMLAH</th>';
							echo '<th width="15%">TERIMA</th>';
							echo '<th width="25%">HARGA</th>';
				echo '</tr>
</thead>';
				$list_result = $list_d_pembelian->result();
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
							</td>';
						echo'<td>'.number_format($row->jumlah,1,'.',',').' '.$row->nama_satuan.'</td>';
						echo'<td>'.number_format($row->TERIMA,1,'.',',').' '.$row->nama_satuan.'</td>';
						echo'<td>'.number_format($row->harga,1,'.',',').'</td>';
						
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

	function hapus_pembelian()
	{
		//HAPUS tb_h_pembelian
			$this->M_gl_pst_lap_pembelian->hapus("tb_h_pembelian", " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_pembelian = '".$_POST['id_h_pembelian']."'");
		//HAPUS tb_h_pembelian
		
		//HAPUS tb_d_pembelian
			$this->M_gl_pst_lap_pembelian->hapus("tb_d_pembelian", " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_pembelian = '".$_POST['id_h_pembelian']."'");
		//HAPUS tb_d_pembelian
		
		//HAPUS tb_d_pembelian_bayar
			$this->M_gl_pst_lap_pembelian->hapus("tb_d_pembelian_bayar", " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_pembelian = '".$_POST['id_h_pembelian']."'");
		//HAPUS tb_d_pembelian_bayar
		
		//HAPUS tb_h_penerimaan
			$this->M_gl_pst_lap_pembelian->hapus("tb_h_penerimaan", " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_pembelian = '".$_POST['id_h_pembelian']."'");
		//HAPUS tb_h_penerimaan
		
		//HAPUS tb_d_penerimaan
			$this->M_gl_pst_lap_pembelian->hapus("tb_d_penerimaan", " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_pembelian = '".$_POST['id_h_pembelian']."'");
		//HAPUS tb_d_penerimaan
		
		echo"TERHAPUS";
	}

	function tampilkan_lap_detail_pembelian_order()
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

				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				} else {
					$kode_kantor = '';
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							AND COALESCE(B.sts_pembelian,'') = 'SELESAI'
							AND COALESCE(DATE(B.tgl_h_pembelian),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."' 
							AND 
							(
								COALESCE(B.no_h_pembelian,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(D.kode_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(D.nama_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)
							";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							AND COALESCE(B.sts_pembelian,'') = 'SELESAI'
							AND COALESCE(DATE(B.tgl_h_pembelian),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."' 
							";
				}
				$order_by = "ORDER BY A.tgl_ins DESC";
				
				
				//UNTUK AKUMULASI INFO
					$jum_row = $this->M_gl_pst_lap_pembelian->count_lap_d_pembelian($cari)->JUMLAH;
				//UNTUK AKUMULASI INFO
				
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				//$config['first_url'] = site_url('gl-admin-laporan-transaksi?'.http_build_query($_GET));
				$config['first_url'] = site_url('gl-pusat-admin-laporan-pembelian-lap-detail-produk-order?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-pusat-admin-laporan-pembelian-lap-detail-produk-order/');
				$config['total_rows'] = $jum_row;
				$config['uri_segment'] = 2;	
				//$config['uri_segment'] = $_GET['var_offset'];	
				$config['per_page'] = 30;
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
				
				$list_laporan_h_pembelian_detail_order = $this->M_gl_pst_lap_pembelian->list_lap_d_pembelian($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				
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
				
				
				$msgbox_title = " Laporan Detail Order Produk Ke Supplier ";
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("")->result();
				
				$data = array(
							'page_content'=>'gl_pusat_admin_lap_h_pembelian_detail_order',
							'halaman'=> $halaman,
							'msgbox_title' => $msgbox_title,
							'sum_pesan'=> $sum_pesan,
							'list_laporan_h_pembelian_detail_order' => $list_laporan_h_pembelian_detail_order,
							'list_kantor' => $list_kantor,
						);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	function tampilkan_lap_detail_pembelian_order_terima()
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

				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				} else {
					$kode_kantor = '';
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							AND COALESCE(B.sts_pembelian,'') = 'SELESAI'
							AND COALESCE(DATE(B.tgl_h_pembelian),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."' 
							AND A.jumlah > COALESCE(D.TERIMA,0)
							AND 
							(
								COALESCE(B.no_h_pembelian,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(E.kode_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(E.nama_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)
							";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							AND COALESCE(B.sts_pembelian,'') = 'SELESAI'
							AND COALESCE(DATE(B.tgl_h_pembelian),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
							AND A.jumlah > COALESCE(D.TERIMA,0)
							";
				}
				$order_by = "ORDER BY A.tgl_ins DESC";
				
				
				//UNTUK AKUMULASI INFO
					$jum_row = $this->M_gl_pst_lap_pembelian->count_lap_d_pembelian_with_penerimaan($cari)->JUMLAH;
				//UNTUK AKUMULASI INFO
				
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				//$config['first_url'] = site_url('gl-admin-laporan-transaksi?'.http_build_query($_GET));
				$config['first_url'] = site_url('gl-pusat-admin-laporan-pembelian-lap-detail-produk-penerimaan?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-pusat-admin-laporan-pembelian-lap-detail-produk-penerimaan/');
				$config['total_rows'] = $jum_row;
				$config['uri_segment'] = 2;	
				//$config['uri_segment'] = $_GET['var_offset'];	
				$config['per_page'] = 30;
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
				
				$list_laporan_h_pembelian_detail_order_terima = $this->M_gl_pst_lap_pembelian->list_lap_d_pembelian_with_penerimaan_dipakai_laporan($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				
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
				
				
				$msgbox_title = " Laporan Detail Penerimaan Order Produk Dari Supplier ";
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("")->result();
				
				$data = array(
						'page_content'=>'gl_pusat_admin_lap_h_pembelian_detail_order_terima',
						'halaman'=>$halaman,
						'msgbox_title' => $msgbox_title,
						'sum_pesan'=>$sum_pesan,
						'list_laporan_h_pembelian_detail_order_terima' => $list_laporan_h_pembelian_detail_order_terima,
						'list_kantor' => $list_kantor,
					);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	function tampilkan_list_hutang_supplier_vipot()
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
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = '';
				}
				
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
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = $_GET['cari'];
				}
				else
				{
					$cari = "";
				}
				
				
				$list_lap_hutang_view = $this->M_gl_pst_lap_pembelian->list_hutang_vipot($kode_kantor,$cari,$dari,$sampai);
				if(!empty($list_lap_hutang_view))
				{
					$list_field = $list_lap_hutang_view->field_data();
				}
				else
				{
					$list_field = false;
				}
				
				
				$msgbox_title = " Laporan Hutang Tempo Supplier ";
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("")->result();
				
				$data = array(
						'page_content'=>'gl_pusat_admin_lap_hutang_supplier_tempo_vipot',
						'msgbox_title' => $msgbox_title,
						'list_lap_hutang_view' => $list_lap_hutang_view,
						'list_field' => $list_field,
						'list_kantor' => $list_kantor,
					);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function tampilkan_list_hutang_supplier_vipot_excel()
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
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
				
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
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = $_GET['cari'];
				}
				else
				{
					$cari = "";
				}
				
				
				$list_lap_hutang_view = $this->M_gl_pst_lap_pembelian->list_hutang_vipot($kode_kantor,$cari,$dari,$sampai);
				if(!empty($list_lap_hutang_view))
				{
					$list_field = $list_lap_hutang_view->field_data();
				}
				else
				{
					$list_field = false;
				}
				
				
				$data = array('page_content'=>'gl_admin_excel_lap_hutang_supplier_tempo_vipot','list_lap_hutang_view' => $list_lap_hutang_view,'list_field' => $list_field);
				$this->load->view('admin/page/gl_admin_excel_lap_hutang_supplier_tempo_vipot.html',$data);
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