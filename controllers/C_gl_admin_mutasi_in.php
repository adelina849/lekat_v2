<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_mutasi_in extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_pengaturan','M_gl_lap_penjualan','M_gl_h_mutasi','M_gl_produk','M_gl_satuan_konversi'));
		
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
				if(!empty($this->uri->segment(2,0)))
				{
					$id_h_mutasi = $this->uri->segment(2,0);
					
					$cari_h_mutasi = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_h_penjualan) = '".$id_h_mutasi."'";
					
					$order_by = " ORDER BY A.tgl_ins DESC";
					$data_h_mutasi = $this->M_gl_h_mutasi->list_h_mutasi($cari_h_mutasi,$order_by,1,0);
					if(!empty($data_h_mutasi))
					{
						$data_h_mutasi = $data_h_mutasi->row();
						
						$cari_data_kantor = " WHERE kode_kantor = '".$data_h_mutasi->kode_kantor_mutasi."'";
						$data_kantor = $this->M_gl_pengaturan->get_data_kantor($cari_data_kantor);
						if(!empty($data_kantor))
						{
							$data_kantor = $data_kantor->row();
						}
						else
						{
							$data_kantor = false;
						}
					}
					else
					{
						$data_h_mutasi = false;
						$data_kantor = false;
					}
				}
				else
				{
					$data_h_mutasi = false;
					$data_kantor = false;
				}
				
				
				$msgbox_title = " Mutasi In/Pemasukan/Penambahan Produk";
				
				$data = array('page_content'=>'gl_admin_h_mutasi_in','msgbox_title' => $msgbox_title,'data_h_mutasi' => $data_h_mutasi,'data_kantor' => $data_kantor);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_h_mutasi()
	{	
		
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = 
			"
				WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
				AND A.sts_penjualan = 'SELESAI' 
				AND A.status_penjualan = 'MUTASI-IN'
				AND DATE(A.tgl_h_penjualan) = DATE(NOW())
				AND (A.no_faktur LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.ket_penjualan LIKE '%".str_replace("'","",$_POST['cari'])."%')
			";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
			AND A.sts_penjualan = 'SELESAI'
			AND A.status_penjualan = 'MUTASI-IN'
			AND DATE(A.tgl_h_penjualan) = DATE(NOW())
			";
		}
	
		$order_by = " ORDER BY A.tgl_ins DESC";
		$list_mutasi = $this->M_gl_h_mutasi->list_h_mutasi($cari,$order_by,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_mutasi))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="30%">FAKTUR</th>';
							echo '<th width="50%">KETERANGAN</th>';
							echo '<th width="15%">AKSI</th>';
				echo '</tr>
</thead>';
				$list_result = $list_mutasi->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_pendaftaran-'.$no.'">';
					echo'<td>'.$no.'</td>';
					
						echo '<td>'.$row->no_faktur.'</td>';
						
						echo'<td><b>KUNJUNGAN : </b>'.$row->no_faktur_penjualan.'
								<br/><b>KETERANGAN : </b>'.$row->ket_penjualan.' 
							</td>';
						
						/*DENHGAN ENRYPTION */
						echo'<td>
								<button type="button" id="'.$row->id_h_penjualan.'" onclick="cetak_ulang_faktur(this)" class="btn btn-primary btn-block btn-flat">PRINT</button>
								
								<a href="'.base_url().'gl-admin-mutasi-in/'.(md5($row->id_h_penjualan)).'" class="confirm-btn btn btn-success btn-block btn-flat" title="Apakah yakin akan mengubah mutasi out '.$row->no_faktur.' ?" alt="Apakah yakin akan mengubah mutasi out '.$row->no_faktur.' ?">UBAH</a>
								
								<a href="'.base_url().'gl-admin-mutasi-out-hapus-all?hapus='.(md5($row->id_h_penjualan)).'" class="confirm-btn btn btn-danger btn-block btn-flat" title="Apakah yakin akan menghapus mutasi '.$row->no_faktur.' ?" alt="Apakah yakin akan menghapus mutasi '.$row->no_faktur.' ?" >HAPUS</a>
							</td>';
						/*DENHGAN ENRYPTION */
							
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
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_mutasi_in.php */