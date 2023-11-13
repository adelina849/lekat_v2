<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_pst_admin_lap_mutasi extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_h_mutasi'));
		
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
				
				if((!empty($_GET['jenis_mutasi'])) && ($_GET['jenis_mutasi']!= "")  )
				{
					$jenis_mutasi = $_GET['jenis_mutasi'];
				}
				else
				{
					$jenis_mutasi = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' AND COALESCE(DATE(B.tgl_h_penjualan),'') BETWEEN '".$dari."' AND '".$sampai."'
							AND COALESCE(B.status_penjualan,'') LIKE '%".$jenis_mutasi."%'
							AND (
								
								COALESCE(C.nama_kantor,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(B.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(D.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(D.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
							";
				}
				else
				{
					$cari = "
						WHERE A.kode_kantor = '".$kode_kantor."' 
						AND COALESCE(DATE(B.tgl_h_penjualan),'') BETWEEN '".$dari."' AND '".$sampai."' 
						AND COALESCE(B.status_penjualan,'') LIKE '%".$jenis_mutasi."%'
						";
				}
				
				$order_by = "ORDER BY A.tgl_ins DESC";
				
				$list_h_mutasi = $this->M_gl_h_mutasi->list_lap_detail_mutasi($cari,$order_by,1000000,0);
			
				$msgbox_title = " Laporan Mutasi Produk";
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("")->result();
				
				$data = array(
						'page_content'=>'gl_pusat_admin_lap_h_mutasi',
						'list_h_mutasi'=>$list_h_mutasi,
						'msgbox_title' => $msgbox_title,
						'list_kantor' => $list_kantor
					);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_mutasi_dari_cabang_lain()
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
							WHERE A.kode_kantor = '".$kode_kantor."' AND COALESCE(DATE(B.tgl_h_penjualan),'') BETWEEN '".$dari."' AND '".$sampai."'
							AND COALESCE(B.status_penjualan,'') = 'MUTASI-OUT'
							AND (
								A.kode_kantor_mutasi LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(C.nama_kantor,'') LIKE '%".str_replace("'","",$_GET['cari'])."%')
								OR COALESCE(B.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%')
								OR COALESCE(D.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%')
								OR COALESCE(D.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%')
							";
				}
				else
				{
					$cari = "
						WHERE A.kode_kantor = '".$kode_kantor."' 
						AND COALESCE(DATE(B.tgl_h_penjualan),'') BETWEEN '".$dari."' AND '".$sampai."' 
						AND COALESCE(B.status_penjualan,'') = 'MUTASI-OUT'
						";
				}
				
				$order_by = "ORDER BY COALESCE(B.no_faktur,'') DESC,COALESCE(D.nama_produk,'') ASC";
				
				$list_h_mutasi = $this->M_gl_h_mutasi->list_lap_detail_mutasi($cari,$order_by,1000000,0);
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("")->result();
			
				$msgbox_title = " Laporan Mutasi Produk";
				
				$data = array(
							'page_content'=>'gl_pusat_admin_lap_h_mutasi_dari_cabang_lain',
							'list_h_mutasi'=>$list_h_mutasi,
							'msgbox_title' => $msgbox_title,
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
	
	public function list_mutasi_dari_cabang_lain_forus()
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
				
				if((!empty($_GET['jenis_mutasi'])) && ($_GET['jenis_mutasi']!= "")  )
				{
					$jenis_mutasi = $_GET['jenis_mutasi'];
				}
				else
				{
					$jenis_mutasi = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE B.kode_kantor_mutasi = '".$kode_kantor."' AND COALESCE(DATE(B.tgl_h_penjualan),'') BETWEEN '".$dari."' AND '".$sampai."'
							-- AND COALESCE(B.status_penjualan,'') LIKE '%".$jenis_mutasi."%'
							AND COALESCE(B.status_penjualan,'') = 'MUTASI-OUT'
							AND (
								
								COALESCE(C.nama_kantor,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(B.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(D.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(D.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
							";
				}
				else
				{
					$cari = "
						WHERE B.kode_kantor_mutasi = '".$kode_kantor."' 
						AND COALESCE(DATE(B.tgl_h_penjualan),'') BETWEEN '".$dari."' AND '".$sampai."' 
						-- AND COALESCE(B.status_penjualan,'') LIKE '%".$jenis_mutasi."%'
						AND COALESCE(B.status_penjualan,'') = 'MUTASI-OUT'
						";
				}
				
				$order_by = "ORDER BY COALESCE(B.no_faktur,'') DESC, COALESCE(D.nama_produk,'') ASC";
				
				$list_h_mutasi = $this->M_gl_h_mutasi->list_lap_detail_mutasi_for_us($cari,$order_by,1000000,0);
			
				$msgbox_title = " Laporan Mutasi IN Produk Dari Cabang Lain";
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("")->result();
				
				$data = array(
						'page_content'=>'gl_pusat_admin_lap_h_mutasi_dari_cabang_lain_forus',
						'list_h_mutasi'=>$list_h_mutasi,
						'msgbox_title' => $msgbox_title,
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
	
	function proses_mutasi_for_us_all()
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
				$id_h_mutasi = $_GET['token'];
				$kode_cabang_asal = $_GET['source'];
				$no_faktur_source = $_GET['description'];

				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				} else {
					$kode_kantor = '';
				}
				
				$cari_h_mutasi = "WHERE MD5(kode_kantor) = '".$kode_cabang_asal."' AND MD5(id_h_penjualan) = '".$id_h_mutasi."' ";
				$get_data_mutasi = $this->M_gl_h_mutasi->get_h_mutasi_by_query($cari_h_mutasi);
				if(!empty($get_data_mutasi))
				{
					$get_data_mutasi = $get_data_mutasi->row();
					
					$get_id_h_mutasi = $this->M_gl_h_mutasi->get_id_h_penjualan_mutasi($this->session->userdata('ses_kode_kantor'));
					$get_id_h_mutasi = $get_id_h_mutasi->id_h_penjualan;
					
					$get_no_faktur = $this->M_gl_h_mutasi->get_no_faktur_mutasi('MUTASI-IN',$this->session->userdata('ses_kode_kantor'));
					//$get_no_faktur = $get_no_faktur->row();
					$no_faktur = $get_no_faktur->NO_FAKTUR;
					
					
					$this->M_gl_h_mutasi->simpan_h_mutasi
					(
						$get_id_h_mutasi,
						'', //$id_h_pemesanan,
						'', //$id_h_retur,
						'', //$id_gudang_dari,
						'', //$id_gudang_ke,
						'', //$id_costumer,
						'', //$id_karyawan,
						$get_data_mutasi->kode_kantor, //$kode_kantor_mutasi,
						$no_faktur,
						$no_faktur_source, //$no_faktur_penjualan,
						0, //$biaya,
						0, //$nominal_retur,
						0, //$bayar,
						'TUNAI', //$isTunai,
						DATE('Y-m-d'), //$tgl_pengajuan,
						DATE('Y-m-d'), //$tgl_h_penjualan,
						DATE('Y-m-d'), //$tgl_tempo,
						'MUTASI-IN', //$status_penjualan,
						'', //$ket_penjualan,
						0, //$type_h_penjualan,
						0, //$acc_mutasi,
						$this->session->userdata('ses_id_karyawan'), //$user_updt,
						$this->session->userdata('ses_kode_kantor'), //$kode_kantor,
						'SELESAI' //$sts_penjualan
					);
					
					
					//DIGANTI INI KAN INSERT NTO LANGSUNG, DIGANTI JADI PROCEDURE LOPING
						//$this->M_gl_h_penjualan->simpan_penjualan_dari_req($this->session->userdata('ses_kode_kantor'),$id_h_penjualan,$id_h_pembelian,$kode_cabang_pemesan);
					//DIGANTI INI KAN INSERT NTO LANGSUNG, DIGANTI JADI PROCEDURE LOPING
					$this->M_gl_h_mutasi->simpan_d_mutasi_dari_req_by_procedure($this->session->userdata('ses_kode_kantor'),$get_id_h_mutasi,$get_data_mutasi->id_h_penjualan,$get_data_mutasi->kode_kantor);
					
					
					header('Location: '.base_url().'gl-admin-mutasi-in/'.md5($get_id_h_mutasi));
				}
				else
				{
					echo'<h1>Ada masalah dengan mutasi dari sumber, silahkan periksa dan ulangi kembali</h1>';
				}
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	
	function list_d_mutasi()
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
				
				$cari_mutasi = " WHERE A.kode_kantor = '".$_POST['kode_kantor']."' AND A.id_h_penjualan = '".$_POST['id_h_penjualan']."'";
				$order_by = " ORDER BY COALESCE(B.nama_produk,'') ASC";
				
				$list_d_mutasi = $this->M_gl_h_mutasi->list_d_mutasi_query($cari_mutasi,$order_by);
				
				if(!empty($list_d_mutasi))
				{
					echo'<table width="100%" id="example2" class="table table-hover">';
						echo '<thead>
		<tr>';
									echo '<th width="5%">NO</th>';
									echo '<th width="15%">KODE</th>';
									echo '<th width="35%">NAMA</th>';
									echo '<th width="45%">TERIMA</th>';
						echo '</tr>
		</thead>';
						$list_result = $list_d_mutasi->result();
						$no =$this->uri->segment(2,0)+1;
						echo '<tbody>';
						foreach($list_result as $row)
						{
							echo'<tr>';
								echo'<td>'.$no.'</td>';
								
								echo '<td>'.$row->kode_produk.'</td>';
								echo '<td>'.$row->nama_produk.'</td>';
								echo '<td>'.$row->jumlah.' '.$row->satuan_jual.'</td>';
								
							echo'</tr>';
							$no++;
						}
						
						echo '</tbody>';
					echo'</table>';
				}
				else
				{
					echo'<center>';
					echo'Tidak Ada Data Yang Ditampilkan !';
					echo'</center>';
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
/* Location: ./application/controllers/C_gl_admin_lap_mutasi.php */