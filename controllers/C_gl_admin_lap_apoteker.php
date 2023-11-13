<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_lap_apoteker extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_lap_apoteker'));
		
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
				$msgbox_title = " List Pemesanan obat pasien ";
				
				$data = array('page_content'=>'gl_admin_lap_apoteker','msgbox_title' => $msgbox_title);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_ajax_apoteker()
	{	
	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "
					WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
					-- AND COALESCE(C.isProduk,'') IN ('PRODUK','CONSUMABLE')
					AND DATE(COALESCE(B.tgl_h_penjualan,NOW())) = DATE(NOW())
					-- AND COALESCE(F.NOMINAL,0) > 0
					AND 
					(
						COALESCE(B.no_faktur,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						OR COALESCE(B.no_costmer,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(B.nama_costumer,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(D.nama_karyawan,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(E.nama_karyawan,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
					)";
		}
		else
		{
			$cari = "
					WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
					-- AND COALESCE(C.isProduk,'') IN ('PRODUK','CONSUMABLE')
					AND DATE(COALESCE(B.tgl_h_penjualan,NOW())) = DATE(NOW())
					-- AND COALESCE(F.NOMINAL,0) > 0
				";
		}
		$order_by = "ORDER BY COALESCE(B.id_h_penjualan,'') DESC, A.tgl_ins DESC";
		
		
		$list_apoteker = $this->M_gl_lap_apoteker->list_apoteker_today($cari,$order_by);
	
	
		echo'<div class="box-body table-responsive no-padding">';
		if(!empty($list_apoteker))
		{
			echo'<table width="100%" id="example2" class="table table-hover hoverTable">';
				echo '<thead>';
				echo'
						<tr style="background-color:green;text-align:center;">';
							echo '<th width="5%">NO</th>';
							echo '<th width="30%">TRANSAKSI</th>';
							echo '<th width="30%">PRODUK</th>';
							echo '<th width="5%">JUMLAH</th>';
							echo '<th width="5%">STATUS</th>';
							echo '<th width="20%">KETERANGAN</th>';
							echo '<th width="5%">HAPUS</th>';
				echo '</tr>
				</thead>';
				$list_result = $list_apoteker->result();
				
				
				$no = $this->uri->segment(2,0) + 1;
				
				$no_faktur_old = "";
				$no_faktur_cur = "";
				
				echo '<tbody>';
				
				foreach($list_result as $row)
				{
					
					
						
						//GROUP NO FAKTUR
						$no_faktur_cur = $row->no_faktur;
						if($no_faktur_cur != $no_faktur_old)
						{
						echo'<tr><td style="background-color:#DCDCDC;height:0.5px;" colspan="6"></td></tr>';
						echo'<tr style="">';
							echo'<td>'.$no.'</td>';
							echo '<td>
								<b>FAKTUR : </b>'.$row->no_faktur.'
								<br/> <b>WAKTU INPUT : </b>'.$row->waktu_input.'
								<br/> <b>NO PELANGGAN : </b>'.$row->no_costumer.'
								<br/> <b>NAMA : </b>'.$row->nama_costumer.'
							</td>';
							
							$no++;
						}
						else
						{
						echo'<tr>';
							echo'<td></td>';
							echo'<td></td>';
						}
						//GROUP NO FAKTUR
						if($row->isProduk == 'CONSUMABLE')
						{
							echo '<td style="color:red;">
							<b>KODE : </b>'.$row->kode_produk.'
							<br/> <b>NAMA : </b>'.$row->nama_produk.'
							</td>';
						}
						elseif($row->isProduk == 'JASA')
						{
							echo '<td style="color:blue;">
							<b>KODE : </b>'.$row->kode_produk.'
							<br/> <b>NAMA : </b>'.$row->nama_produk.'
							</td>';
						}
						else
						{
							echo '<td>
							<b>KODE : </b>'.$row->kode_produk.'
							<br/> <b>NAMA : </b>'.$row->nama_produk.'
							</td>';
						}
						
						
						echo'<td>'.$row->jumlah.' '.$row->satuan_jual.'</td>';
						//echo '<td>'.number_format($row->harga,0,'.',',').' </td>';
						//<input type="checkbox" checked="checked">
						
						if($row->isReady == "1")
						{
							echo '<td style="text-align:center;">
							  <input type="checkbox" name="ready-'.$row->id_d_penjualan.'-'.$row->id_produk.'" id="ready-'.$row->id_d_penjualan.'-'.$row->id_produk.'" onclick="ubah_ready(this)" checked="checked">
							</td>';
						}
						else
						{
							echo '<td style="text-align:center;">
							  <input type="checkbox" name="ready-'.$row->id_d_penjualan.'-'.$row->id_produk.'" id="ready-'.$row->id_d_penjualan.'-'.$row->id_produk.'" onclick="ubah_ready(this)" >
							</td>';
						}
						
						
						echo'<td>
							<textarea name="ket_ready-'.$row->id_d_penjualan.'-'.$row->id_produk.'" id="ket_ready-'.$row->id_d_penjualan.'-'.$row->id_produk.'" class="ket_obat form-control" title="Isikan lengkap tentang data "'.$row->nama_produk.'"" placeholder="*Isikan lengkap tentang data '.$row->nama_produk.'" onchange="ubah_ready(this)">'.$row->ket_ready.'</textarea>
						</td>';
						
						if($row->isProduk == 'CONSUMABLE')
						{
							echo'
							<td>
								<a href="'.base_url().'gl-admin-laporan-apoteker-hapus/'.md5($row->id_d_penjualan).'" class="btn btn-danger btn-flat btn-block" title = "Hapus Data '.$row->nama_produk.'" alt = "Hapus Data '.$row->nama_produk.'">HAPUS</a>
							</td>
							';
						}
						else
						{
							echo'<td></td>';
						}
											
					echo'</tr>';
					
					
					echo'<input type="hidden" id="id_d_penjualan_'.$no.'" name="id_d_penjualan_'.$no.'" value="'.$row->id_d_penjualan.'" />';
					
					//GROUP NO FAKTUR
						$no_faktur_old = $row->no_faktur;
					//GROUP NO FAKTUR
					
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
		//TABLE
					
		echo'
	</div><!-- /.box-body -->
	';
										
	}
	
	function ubah_status_ready()
	{
		$this->M_gl_lap_apoteker->ubah_status_ready($this->session->userdata('ses_kode_kantor'), $_POST['id_d_penjualan'], $_POST['id_produk'], $_POST['isReady'], $_POST['ket_ready']);
		echo"BERHASIL";
	}
	
	public function front_desk()
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
				$msgbox_title = " Front Desk/Meja Depan Pemesanan obat pasien ";
				
				$data = array('page_content'=>'gl_admin_lap_apoteker_front_desk','msgbox_title' => $msgbox_title);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	function list_ajax_apoteker_front_desk()
	{	
	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "
					WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
					AND COALESCE(C.isProduk,'') IN ('PRODUK','CONSUMABLE')
					AND DATE(COALESCE(B.tgl_h_penjualan,NOW())) = DATE(NOW())
					AND COALESCE(F.NOMINAL,0) > 0
					AND 
					(
						COALESCE(B.no_faktur,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						OR COALESCE(B.no_costmer,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(B.nama_costumer,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(D.nama_karyawan,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(E.nama_karyawan,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
					)";
		}
		else
		{
			$cari = "
					WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
					AND COALESCE(C.isProduk,'') IN ('PRODUK','CONSUMABLE')
					AND DATE(COALESCE(B.tgl_h_penjualan,NOW())) = DATE(NOW())
					AND COALESCE(F.NOMINAL,0) > 0
				";
		}
		$order_by = "ORDER BY COALESCE(B.id_h_penjualan,'') DESC, A.tgl_ins DESC";
		
		
		$list_apoteker = $this->M_gl_lap_apoteker->list_apoteker_today($cari,$order_by);
	
	
		echo'<div class="box-body table-responsive no-padding">';
		if(!empty($list_apoteker))
		{
			echo'<table width="100%" id="example2" class="table table-hover hoverTable">';
				echo '<thead>';
				echo'
						<tr style="background-color:green;text-align:center;">';
							echo '<th width="5%">NO</th>';
							echo '<th width="30%">TRANSAKSI</th>';
							echo '<th width="25%">PRODUK</th>';
							echo '<th width="10%">JUMLAH</th>';
							echo '<th width="5%">STATUS</th>';
							echo '<th width="20%">KETERANGAN</th>';
							echo '<th width="5%">DITERIMA</th>';
							
				echo '</tr>
				</thead>';
				$list_result = $list_apoteker->result();
				
				
				$no = $this->uri->segment(2,0) + 1;
				
				$no_faktur_old = "";
				$no_faktur_cur = "";
				
				echo '<tbody>';
				
				foreach($list_result as $row)
				{
					
					
						
						//GROUP NO FAKTUR
						$no_faktur_cur = $row->no_faktur;
						if($no_faktur_cur != $no_faktur_old)
						{
						echo'<tr><td style="background-color:#DCDCDC;height:0.5px;" colspan="7"></td></tr>';
						echo'<tr style="">';
							echo'<td>'.$no.'</td>';
							echo '<td>
								<b>FAKTUR : </b>'.$row->no_faktur.'
								<br/> <b>WAKTU INPUT : </b>'.$row->waktu_input.'
								<br/> <b>NO PELANGGAN : </b>'.$row->no_costumer.'
								<br/> <b>NAMA PELANGGAN : </b>'.$row->nama_costumer.'
							</td>';
							
							$no++;
						}
						else
						{
						echo'<tr style="">';
							echo'<td></td>';
							echo'<td></td>';
						}
						//GROUP NO FAKTUR
						
						echo '<td>
							<b>KODE : </b>'.$row->kode_produk.'
							<br/> <b>NAMA : </b>'.$row->nama_produk.'
						</td>';
						
						echo'<td>'.$row->jumlah.' '.$row->satuan_jual.'</td>';
						//echo '<td>'.number_format($row->harga,0,'.',',').' </td>';
						//<input type="checkbox" checked="checked">
						
						
						if($row->isReady == "1")
						{
							echo '<td style="text-align:center;">
							  <input class="input_ready" type="checkbox" name="ready-'.$row->id_d_penjualan.'-'.$row->id_produk.'" id="ready-'.$row->id_d_penjualan.'-'.$row->id_produk.'" checked="checked" disabled="true">
							</td>';
						}
						else
						{
							echo '<td style="text-align:center;">
							  <input class="input_not_ready" type="checkbox" name="ready-'.$row->id_d_penjualan.'-'.$row->id_produk.'" id="ready-'.$row->id_d_penjualan.'-'.$row->id_produk.'" disabled="true">
							</td>';
						}
						
						echo'<td>
							<textarea name="ket_ready-'.$row->id_d_penjualan.'-'.$row->id_produk.'" id="ket_ready-'.$row->id_d_penjualan.'-'.$row->id_produk.'" class="ket_obat form-control" title="Isikan lengkap tentang data "'.$row->nama_produk.'"" readonly>'.$row->ket_ready.'</textarea>
						</td>';
						
						if($row->isReady == "1")
						{
							if($row->isTerima  == "1")
							{
								echo '<td style="text-align:center;">
								  <input type="checkbox" name="terima-'.$row->id_d_penjualan.'-'.$row->id_produk.'" id="terima-'.$row->id_d_penjualan.'-'.$row->id_produk.'" onclick="ubah_ready(this)" checked="checked">
								</td>';
							}
							else
							{
								echo '<td style="text-align:center;">
								  <input type="checkbox" name="terima-'.$row->id_d_penjualan.'-'.$row->id_produk.'" id="terima-'.$row->id_d_penjualan.'-'.$row->id_produk.'" onclick="ubah_ready(this)" >
								</td>';
							}
						}
						else
						{
							echo '<td style="text-align:center;">
								  <input type="checkbox" name="terima-'.$row->id_d_penjualan.'-'.$row->id_produk.'" id="terima-'.$row->id_d_penjualan.'-'.$row->id_produk.'" disabled="true" >
								</td>';
						}
						
											
					echo'</tr>';
					
					
					echo'<input type="hidden" id="id_d_penjualan_'.$no.'" name="id_d_penjualan_'.$no.'" value="'.$row->id_d_penjualan.'" />';
					
					//GROUP NO FAKTUR
						$no_faktur_old = $row->no_faktur;
					//GROUP NO FAKTUR
					
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
		//TABLE
					
		echo'
	</div><!-- /.box-body -->
	';
										
	}
	
	function ubah_status_terima()
	{
		$this->M_gl_lap_apoteker->ubah_status_terima($this->session->userdata('ses_kode_kantor'), $_POST['id_d_penjualan'], $_POST['id_produk'], $_POST['isTerima']);
		echo"BERHASIL";
	}
	
	
	public function hapus_d_penjualan_apoteker()
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
				$cari = "WHERE MD5(id_d_penjualan) = '".$this->uri->segment(2,0)."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
				
				$this->M_gl_lap_apoteker->hapus_d_penjualan_cari($cari);
				header('Location: '.base_url().'gl-admin-laporan-apoteker');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_lap_apoteker.php */