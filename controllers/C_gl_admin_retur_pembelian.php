<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_retur_pembelian extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_supplier','M_gl_h_pembelian','M_gl_h_retur','M_gl_produk','M_gl_satuan_konversi','M_gl_costumer','M_gl_stock_produk'));
		
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
					$id_h_retur = $this->uri->segment(2,0);
					
					$cari_h_retur = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_h_penjualan) = '".$id_h_retur."'";
					
					$order_by = " ORDER BY A.tgl_ins DESC";
					$data_h_retur = $this->M_gl_h_retur->list_h_retur_supplier($cari_h_retur,$order_by,1,0);
					if(!empty($data_h_retur))
					{
						$data_h_retur = $data_h_retur->row();
						
						$cari_data_supplier = " WHERE A.kode_kantor = '".$data_h_retur->kode_kantor."' AND A.id_supplier = '".$data_h_retur->id_supplier."'";
						$data_supplier = $this->M_gl_supplier->list_supplier_limit($cari_data_supplier,1,0);
						if(!empty($data_supplier))
						{
							$data_supplier = $data_supplier->row();
						}
						else
						{
							$data_supplier = false;
						}
					}
					else
					{
						$data_h_retur = false;
						$data_supplier = false;
					}
				}
				else
				{
					$data_h_retur = false;
					$data_supplier = false;
				}
				
				
				$msgbox_title = " Retur Ke Supplier/Pembelian";
				
				$data = array('page_content'=>'gl_admin_retur_pembelian','msgbox_title' => $msgbox_title,'data_h_retur' => $data_h_retur,'data_supplier' => $data_supplier);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function index_retur_penjualan()
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
					$id_h_retur = $this->uri->segment(2,0);
					
					$cari_h_retur = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_h_penjualan) = '".$id_h_retur."'";
					
					$order_by = " ORDER BY A.tgl_ins DESC";
					$data_h_retur = $this->M_gl_h_retur->list_h_retur_supplier($cari_h_retur,$order_by,1,0);
					if(!empty($data_h_retur))
					{
						$data_h_retur = $data_h_retur->row();
						
						$cari_data_costumer = " WHERE kode_kantor = '".$data_h_retur->kode_kantor."' AND id_costumer = '".$data_h_retur->id_costumer."'";
						$data_costumer = $this->M_gl_costumer->get_costumer_cari($cari_data_costumer);
						if(!empty($data_costumer))
						{
							$data_costumer = $data_costumer->row();
						}
						else
						{
							$data_costumer = false;
						}
					}
					else
					{
						$data_h_retur = false;
						$data_costumer = false;
					}
				}
				else
				{
					$data_h_retur = false;
					$data_costumer = false;
				}
				
				
				$msgbox_title = " Retur Produk Dari Pelanggan (Retur Penjualan)";
				
				$data = array('page_content'=>'gl_admin_retur_penjualan','msgbox_title' => $msgbox_title,'data_h_retur' => $data_h_retur,'data_costumer' => $data_costumer);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_h_pembelian()
	{	
		if((!empty($_POST['dari'])) && ($_POST['dari']!= "")  )
		{
			$dari = $_POST['dari'];
			$sampai = $_POST['sampai'];
		}
		else
		{
			$dari = date("Y-m-d");
			$sampai = date("Y-m-d");
		}
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.sts_pembelian IN ('SELESAI','PEMBAYARAN')
					AND A.status_penjualan = 'RETUR-PEMBELIAN'
					-- AND A.sts_penjualan <> 'SELESAI'
					AND DATE(A.tgl_h_pembelian) BETWEEN '".$dari."' AND '".$sampai."'
					AND 
					(
						A.no_h_pembelian LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						OR COALESCE(B.kode_supplier,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(B.nama_supplier,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(A.ket_h_pembelian,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						
					)";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.sts_pembelian IN ('SELESAI','PEMBAYARAN') 
					AND A.status_penjualan = 'RETUR-PEMBELIAN'
					-- AND A.sts_penjualan <> 'SELESAI'
					AND DATE(A.tgl_h_pembelian) BETWEEN '".$dari."' AND '".$sampai."' ";
		}
		
		$order_by = "ORDER BY A.tgl_ins DESC";
		
		$list_h_pembelian = $this->M_gl_h_retur->list_h_pembelian($cari,$order_by,30,0);
		
		if(!empty($list_h_pembelian))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="35%">FAKTUR</th>';
							echo '<th width="45%">SUPPLIER</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_h_pembelian->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_list_penjualan-'.$no.'">';
						echo'<td>'.$no.'</td>';
						echo'<td>
								<b>NO FAKTUR : </b>'.$row->no_h_pembelian.' 
								<b>WAKTU : </b>'.$row->DT_PEMBELIAN.' 
								<br/> <b>KETERANGAN : </b>'.$row->ket_h_pembelian.'
							</td>';
						echo'<td>
								<b>NO SUPPLIER : </b>'.$row->kode_supplier.'
								<br/> <b>NAMA : </b>'.$row->nama_supplier.'
							</td>';
						
						echo'<td>
							<button type="button" onclick="insert_h_pembelian('.$no.')" class="btn btn-success btn-block btn-flat" data-dismiss="modal">PILIH</button>
						</td>';
				
					echo'</tr>';
					
					echo'<input type="hidden" id="id_h_pembelian_5_'.$no.'" name="id_h_pembelian_5_'.$no.'" value="'.$row->id_h_pembelian.'" />';
					echo'<input type="hidden" id="no_h_pembelian_5_'.$no.'" name="no_h_pembelian_5_'.$no.'" value="'.$row->no_h_pembelian.'" />';
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

	
	function list_supplier()
	{	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE 
							kode_kantor =  '".$this->session->userdata('ses_kode_kantor')."'
							AND 
							(
								kode_supplier LIKE '%".str_replace("'","",$_POST['cari'])."%'
								OR nama_supplier LIKE '%".str_replace("'","",$_POST['cari'])."%'
								OR alamat LIKE '%".str_replace("'","",$_POST['cari'])."%'
							)
							
					";
		}
		else
		{
			$cari = "WHERE kode_kantor =  '".$this->session->userdata('ses_kode_kantor')."'";
		}
		
		$order_by = " ORDER BY A.nama_supplier ASC";
		$list_supplier = $this->M_gl_h_retur->list_supplier($cari,$order_by,15,0);
		
		if(!empty($list_supplier))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_kantor" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="35%">KANTOR/CABANG</th>';
							echo '<th width="45%">ALAMAT</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_supplier->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_list_penjualan-'.$no.'">';
						echo'<td>'.$no.'</td>';
						echo'<td>
								<b>KODE : </b>'.$row->kode_supplier.' 
								<br/> <b>NAMA : </b>'.$row->nama_supplier.' 
							</td>';
						echo'<td>
								'.$row->alamat.'
							</td>';
						
						echo'<td>
							<button type="button" onclick="insert_supplier('.$no.')" class="btn btn-success btn-block btn-flat" data-dismiss="modal">PILIH</button>
						</td>';
				
					echo'</tr>';
					
					echo'<input type="hidden" id="id_supplier_6_'.$no.'" name="id_supplier_6_'.$no.'" value="'.$row->id_supplier.'" />';
					echo'<input type="hidden" id="kode_supplier_6_'.$no.'" name="kode_supplier_6_'.$no.'" value="'.$row->kode_supplier.'" />';
					echo'<input type="hidden" id="nama_supplier_6_'.$no.'" name="nama_supplier_6_'.$no.'" value="'.$row->nama_supplier.'" />';
					echo'<input type="hidden" id="tlp_6_'.$no.'" name="tlp_6_'.$no.'" value="'.$row->tlp.'" />';
					echo'<input type="hidden" id="alamat_6_'.$no.'" name="alamat_6_'.$no.'" value="'.$row->alamat.'" />';
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
	
	function list_costumer()
	{	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE 
							kode_kantor =  '".$this->session->userdata('ses_kode_kantor')."'
							AND 
							(
								no_costumer LIKE '%".str_replace("'","",$_POST['cari'])."%'
								OR nama_lengkap LIKE '%".str_replace("'","",$_POST['cari'])."%'
								OR alamat_rumah_sekarang LIKE '%".str_replace("'","",$_POST['cari'])."%'
							)
					 ORDER BY nama_lengkap ASC LIMIT 0,30	
					";
		}
		else
		{
			$cari = "WHERE kode_kantor =  '".$this->session->userdata('ses_kode_kantor')."' ORDER BY nama_lengkap ASC LIMIT 0,30";
		}
		
		$order_by = " ORDER BY nama_lengkap ASC";
		$list_costumer = $this->M_gl_costumer->get_costumer_cari($cari,$order_by,15,0);
		
		if(!empty($list_costumer))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_kantor" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="35%">PELANGGAN</th>';
							echo '<th width="45%">ALAMAT</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_costumer->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_list_penjualan-'.$no.'">';
						echo'<td>'.$no.'</td>';
						echo'<td>
								<b>KODE : </b>'.$row->no_costumer.' 
								<br/> <b>NAMA : </b>'.$row->nama_lengkap.' 
							</td>';
						echo'<td>
								'.$row->alamat_rumah_sekarang.'
							</td>';
						
						echo'<td>
							<button type="button" onclick="insert_supplier('.$no.')" class="btn btn-success btn-block btn-flat" data-dismiss="modal">PILIH</button>
						</td>';
				
					echo'</tr>';
					
					echo'<input type="hidden" id="id_costumer_6_'.$no.'" name="id_costumer_6_'.$no.'" value="'.$row->id_costumer.'" />';
					echo'<input type="hidden" id="no_costumer_6_'.$no.'" name="no_costumer_6_'.$no.'" value="'.$row->no_costumer.'" />';
					echo'<input type="hidden" id="nama_lengkap_6_'.$no.'" name="nama_lengkap_6_'.$no.'" value="'.$row->nama_lengkap.'" />';
					echo'<input type="hidden" id="alamat_rumah_sekarang_6_'.$no.'" name="alamat_rumah_sekarang_6_'.$no.'" value="'.$row->alamat_rumah_sekarang.'" />';
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
	
	function list_produk()
	{	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					-- AND A.isProduk IN ('PRODUK','CONSUMABLE')
					AND A.isProduk <> 'JASA'
					AND B.besar_konversi = 1
					AND E.id_produk IS NULL
					AND (A.kode_produk LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_produk LIKE '%".str_replace("'","",$_POST['cari'])."%')";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.isProduk <> 'JASA' 
					AND B.besar_konversi = 1 AND E.id_produk IS NULL";
		}
		
		$order_by = " ORDER BY A.nama_produk ASC ";
		
		$list_produk = $this->M_gl_produk->list_produk_limit_harga_dasar_untuk_retur_pembelian($_POST['id_h_penjualan'],$cari,$order_by,$_POST['limit'],$_POST['offset']);
		//($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_produk))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="5%">PILIH</th>';
							//echo '<th width="10%">FOTO</th>';
							echo '<th width="60%">DATA PRODUK</th>';
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
						echo'<td><input type="checkbox" name="record"></td>';
						
						/*
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
						*/
						
						//<br/> <b>HARGA : </b><b style="color:red;">'.number_format($row->harga_jual,2,'.',',').' /'.$row->kode_satuan.'</b>
						echo'<td>
								<b>KODE : </b>'.$row->kode_produk.' 
								<br/> <b>NAMA : </b>'.$row->nama_produk.' 
								<br/> <b>PRODUKSI : </b>'.$row->produksi_oleh.'
								<br/> <b>SATUAN : </b><b style="color:red;">'.$row->kode_satuan.'</b>
							</td>';
						echo'<td>
								<!-- <button type="button" onclick="insert_produk('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button> -->
								<button type="button" onclick="insert_produk('.$no.')" class="btn btn-primary btn-sm" >Pilih</button>
							</td>';
							
						echo'<input type="hidden" id="get_tr_3_'.$no.'" name="get_tr_3_'.$no.'" value="tr_list_produk-'.$no.'" />';
						echo'<input type="hidden" id="id_produk_3_'.$no.'" name="id_produk_3_'.$no.'" value="'.$row->id_produk.'" />';
						echo'<input type="hidden" id="kode_produk_3_'.$no.'" name="kode_produk_3_'.$no.'" value="'.$row->kode_produk.'" />';
						echo'<input type="hidden" id="nama_produk_3_'.$no.'" name="nama_produk_3_'.$no.'" value="'.$row->nama_produk.'" />';
						echo'<input type="hidden" id="harga_jual_3_'.$no.'" name="harga_jual_3_'.$no.'" value="'.$row->harga_jual.'" />';
						echo'<input type="hidden" id="id_satuan_3_'.$no.'" name="id_satuan_3_'.$no.'" value="'.$row->id_satuan.'" />';
						echo'<input type="hidden" id="kode_satuan_3_'.$no.'" name="kode_satuan_3_'.$no.'" value="'.$row->kode_satuan.'" />';
						echo'<input type="hidden" id="status_3_'.$no.'" name="status_3_'.$no.'" value="'.$row->status.'" />';
						echo'<input type="hidden" id="besar_konversi_3_'.$no.'" name="besar_konversi_3_'.$no.'" value="'.$row->besar_konversi.'" />';
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

	function simpan_h_retur_awal()
	{
		$get_id_h_penjualan = $this->M_gl_h_retur->get_id_h_penjualan_retur($this->session->userdata('ses_kode_kantor'));
		//$get_id_penjualan = $get_id_penjualan->row();
		$id_h_penjualan = $get_id_h_penjualan->id_h_penjualan;
		
		$get_no_faktur = $this->M_gl_h_retur->get_no_faktur_retur($_POST['jenis'],$this->session->userdata('ses_kode_kantor'));
		//$get_no_faktur = $get_no_faktur->row();
		$no_faktur = $get_no_faktur->NO_FAKTUR;
		
		
		$this->M_gl_h_retur->simpan_h_retur
		(
			$id_h_penjualan, //$id_h_penjualan,
			'', //$id_h_pemesanan,
			$_POST['id_supplier'],
			$_POST['id_costumer'],
			'', //$id_karyawan,
			'', //$id_penerimaan,
			$no_faktur, //$no_faktur,
			$_POST['no_faktur_penjualan'],
			$_POST['biaya'],
			0, //$nominal_retur,
			$_POST['tgl_h_penjualan'], //$tgl_pengajuan,
			$_POST['tgl_h_penjualan'], //$tgl_h_penjualan,
			$_POST['tgl_h_penjualan'], //$tgl_tempo,
			$_POST['jenis'], //$status_penjualan,
			$_POST['ket_penjualan'], //$ket_penjualan,
			$_POST['type_h_penjualan'], //$type_h_penjualan,
			$this->session->userdata('ses_id_karyawan'), //$_POST['user_updt'],
			$this->session->userdata('ses_kode_kantor'), //$_POST['kode_kantor'],
			$_POST['sts_penjualan'], //$sts_penjualan,
			$_POST['alasan_retur'] //$alasan_retur
		);
		
		
		echo $id_h_penjualan.'-'.$no_faktur;
	}
	
	
	function simpan_d_retur()
	{
		$this->M_gl_h_retur->simpan_d_retur
		(
			'', //$id_d_penjualan_retur,
			$_POST['id_h_penjualan'], //$id_h_penjualan,
			'', //$id_h_penjualan_retur,
			'', //$id_d_penerimaan,
			$_POST['id_produk'], //$id_produk,
			$_POST['jumlah'], //$jumlah,
			$_POST['status_konversi'], //$status_konversi,
			$_POST['konversi'], //$konversi,
			$_POST['satuan_jual'], //$satuan_jual,
			$_POST['jumlah_konversi'], //$jumlah_konversi,
			$_POST['harga'], //$harga,
			$_POST['harga_konversi'], //$harga_konversi,
			$_POST['harga_ori'], //$harga_ori,
			$_POST['ket_d_penjualan'], //$ket_d_penjualan,
			$this->session->userdata('ses_id_karyawan'),
			$this->session->userdata('ses_kode_kantor'),
			$_POST['optr_charge'], //$optr_charge,
			$_POST['charge'], //$charge,
			$_POST['diskon'], //$diskon,
			$_POST['kondisi'] //$kondisi
		);
	}
	
	function ubah_d_retur()
	{
		$cari = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' AND id_produk = '".$_POST['id_produk']."'";
			
			$data_d_retur = $this->M_gl_h_retur->get_d_retur_by_query($cari);
			if(!empty($data_d_retur))
			{
				$data_d_retur = $data_d_retur->row();
				
				if(str_replace(",","",$_POST['jumlah']) > 0)
				{
					$this->M_gl_h_retur->ubah_d_retur
					(
						$_POST['id_d_penjualan_retur'], //$id_d_penjualan_retur,
						$_POST['id_h_penjualan'], //$id_h_penjualan,
						$_POST['id_h_penjualan_retur'], //$id_h_penjualan_retur,
						$_POST['id_d_penerimaan'], //$id_d_penerimaan,
						$_POST['id_produk'], //$id_produk,
						$_POST['jumlah'], //$jumlah,
						$_POST['status_konversi'], //$status_konversi,
						$_POST['konversi'], //$konversi,
						$_POST['satuan_jual'], //$satuan_jual,
						$_POST['jumlah_konversi'], //$jumlah_konversi,
						$_POST['harga'], //$harga,
						$_POST['harga_konversi'], //$harga_konversi,
						$_POST['harga_ori'], //$harga_ori,
						$_POST['ket_d_penjualan'], //$ket_d_penjualan,
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor'),
						$_POST['optr_charge'], //$optr_charge,
						$_POST['charge'], //$charge,
						$_POST['diskon'], //$diskon,
						$_POST['kondisi'] //$kondisi
					);
				}
				else
				{
					$cari = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_d_penjualan = '".$data_d_retur->id_d_penjualan."'";
					$this->M_gl_h_retur->hapus_d_retur($cari);
				}
			}
			
		
	}
	
	function ubah_d_retur_kondisi_produk()
	{
		$query = "
				UPDATE tb_d_retur 
				SET kondisi = '".$_POST['kondisi']."' 
				WHERE 
					kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND id_h_penjualan = '".$_POST['id_h_penjualan']."' 
					AND id_produk = '".$_POST['id_produk']."'
				";
				
		$this->M_gl_pengaturan->exec_query_general($query);
		
		echo "BERHASIL";
		
	}

	function batal()
	{
		$cari = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' ";
			
		$data_h_retur = $this->M_gl_h_retur->get_h_retur_by_query($cari);
		if(!empty($data_h_retur))
		{
			$data_h_retur = $data_h_retur->row();
			
			if($data_h_retur->sts_penjualan == 'PENDING')
			{
				//HAPUS H MUTASI
					$this->M_gl_h_retur->hapus_h_retur($cari);
				//HAPUS H MUTASI
				
				//HAPUS D RETUR
					$this->M_gl_h_retur->hapus_d_retur($cari);
				//HAPUS D RETUR
			}
		}
		
		echo"BERHASIL";
	}

	function simpan_selesai()
	{
		$cari = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' ";
			
		$data_h_retur = $this->M_gl_h_retur->get_h_retur_by_query($cari);
		if(!empty($data_h_retur))
		{
			$data_h_retur = $data_h_retur->row();
			
			
			
			$this->M_gl_h_retur->ubah_h_retur
			(
				$_POST['id_h_penjualan'], //$id_h_penjualan,
				$data_h_retur->id_h_pemesanan,
				$_POST['id_supplier'], //$data_h_retur->id_supplier,
				$_POST['id_costumer'], //$data_h_retur->id_costumer,
				$data_h_retur->id_karyawan,
				$data_h_retur->id_penerimaan,
				$_POST['no_faktur'], //$data_h_retur->no_faktur,
				$_POST['no_faktur_penjualan'], //$data_h_retur->no_faktur_penjualan,
				$_POST['biaya'], //$data_h_retur->biaya,
				$_POST['nominal_retur'], //$data_h_retur->nominal_retur,
				$_POST['tgl_h_penjualan'], //$data_h_retur->tgl_pengajuan,
				$_POST['tgl_h_penjualan'], //$data_h_retur->tgl_h_penjualan,
				$_POST['tgl_h_penjualan'], //$data_h_retur->tgl_tempo,
				$data_h_retur->status_penjualan, //$data_h_retur->status_penjualan,
				$_POST['ket_penjualan'], //$data_h_retur->ket_penjualan,
				$data_h_retur->type_h_penjualan,
				$data_h_retur->user_updt,
				$data_h_retur->kode_kantor,
				'SELESAI', //$data_h_retur->sts_penjualan,
				$_POST['alasan_retur'] //$data_h_retur->alasan_retur
			
			);
			echo $_POST['id_supplier'];
		}
	}

	function print_faktur_retur_supplier()
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
					$id_h_penjualan = $this->uri->segment(2,0);
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND MD5(id_h_penjualan) = '".$id_h_penjualan."'
								AND (no_faktur LIKE '%".str_replace("'","",$_GET['cari'])."%')";
					}
					else
					{
						$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(id_h_penjualan) = '".$id_h_penjualan."'";
					}
					
					$order_by = "ORDER BY A.tgl_ins DESC";
					$data_h_retur = $this->M_gl_h_retur->get_h_retur_by_query($cari);
					if(!empty($data_h_retur))
					{
						$data_h_retur = $data_h_retur->row();
						
						/*TAMPILKAN*/
						
						$data = array('page_content'=>'admin/page/gl_admin_print_faktur_mutasi_out.html','data_h_retur' => $data_h_retur);
						$this->load->view('admin/page/gl_admin_print_faktur_mutasi_out.html',$data);
						
						/*TAMPILKAN*/

					}
					else
					{
						//header('Location: '.base_url().'gl-admin-pendaftaran-pasien');
						//echo $id_h_penjualan;
					}
				}
				else
				{
					header('Location: '.base_url().'gl-admin-retur-supplier');
				}
				
				
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	function list_h_retur()
	{	
		if((!empty($_POST['status_penjualan'])) && ($_POST['status_penjualan']!= "")  )
		{
			$status_penjualan = str_replace("'","",$_POST['status_penjualan']);
		}
		else
		{
			$status_penjualan = "RETUR-PEMBELIAN";
		}
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = 
			"
				WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
				-- AND A.sts_penjualan = 'SELESAI' 
				AND A.status_penjualan = '".$status_penjualan."'
				AND DATE(A.tgl_h_penjualan) = DATE(NOW())
				AND (A.no_faktur LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.ket_penjualan LIKE '%".str_replace("'","",$_POST['cari'])."%')
			";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
			-- AND A.sts_penjualan = 'SELESAI'
			AND A.status_penjualan = '".$status_penjualan."'
			AND DATE(A.tgl_h_penjualan) = DATE(NOW())
			";
		}
	
		$order_by = " ORDER BY A.tgl_ins DESC";
		$list_retur = $this->M_gl_h_retur->list_h_retur_supplier($cari,$order_by,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_retur))
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
				$list_result = $list_retur->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_pendaftaran-'.$no.'">';
					echo'<td>'.$no.'</td>';
					
						echo '<td>
								<b>FAKTUR : </b>'.$row->no_faktur.'
								<br/><b>STATUS : </b>'.$row->sts_penjualan.' 
								<br/><b>TANGGAL : </b>'.$row->DT_PENJUALAN.' 
						
							</td>';
						
						echo'<td><b>SUP/CUS : </b>'.$row->NAMA_SUPPLIER.'
								<br/><b>KETERANGAN : </b>'.$row->alasan_retur.' 
							</td>';
						
						/*DENHGAN ENRYPTION */
						echo'<td>
								<button type="button" id="'.$row->id_h_penjualan.'" onclick="cetak_ulang_faktur(this)" class="btn btn-primary btn-block btn-flat">PRINT</button>
								';
								
								if($status_penjualan == "RETUR-PENJUALAN")
								{
									echo'<a href="'.base_url().'gl-admin-retur-pelanggan/'.(md5($row->id_h_penjualan)).'" class="confirm-btn btn btn-success btn-block btn-flat" title="Apakah yakin akan mengubah retur '.$row->no_faktur.' ?" alt="Apakah yakin akan mengubah retur '.$row->no_faktur.' ?">UBAH</a>';
								}
								else
								{
									echo'<a href="'.base_url().'gl-admin-retur-supplier/'.(md5($row->id_h_penjualan)).'" class="confirm-btn btn btn-success btn-block btn-flat" title="Apakah yakin akan mengubah retur '.$row->no_faktur.' ?" alt="Apakah yakin akan mengubah retur '.$row->no_faktur.' ?">UBAH</a>';
								}
								
						echo'
								<a href="'.base_url().'gl-admin-retur-supplier-hapus?hapus='.(md5($row->id_h_penjualan)).'&status='.$status_penjualan.'" class="confirm-btn btn btn-danger btn-block btn-flat" title="Apakah yakin akan menghapus retur '.$row->no_faktur.' ?" alt="Apakah yakin akan menghapus retur '.$row->no_faktur.' ?" >HAPUS</a>
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

	function list_d_retur()
	{	
		
		$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_h_penjualan = '".$_POST['id_h_penjualan']."'";
		
		$order_by = " ORDER BY A.tgl_ins DESC ";
		
		$list_d_retur = $this->M_gl_h_retur->list_d_retur_produk($cari,$order_by,1000,0);
		
		if(!empty($list_d_retur))
		{
				$list_result = $list_d_retur->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_transaksi-'.$row->id_produk.'">';
					
						echo'<td>'.$row->kode_produk.'</td>';
						echo'<td>'.$row->nama_produk.'</td>';
						echo'<td><input type="text" name="jumlah-'.$row->id_produk.'" id="jumlah-'.$row->id_produk.'" maxlength="4" size="4" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)" onchange="edit_d_pembelian(this.id)" class="required " value="'.$row->jumlah.'" /></td>';
						echo'<td><select name="kode_satuan-'.$row->id_produk.'" id="kode_satuan-'.$row->id_produk.'" class="required  select2" onfocus="tampilkan_list_satuan(this.id)" onchange="harga_satuan(this.id)"><option value="'.$row->satuan_jual.'">'.$row->satuan_jual.'</option></select></td>';
						
						echo'<td><input type="text" name="harga_dasar-'.$row->id_produk.'" id="harga_dasar-'.$row->id_produk.'" maxlength="10" size="10" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)" onchange="edit_d_pembelian(this.id)" class="required " value="'.$row->harga.'" /></td>';
						
						echo'<td><select name="kondisi-'.$row->id_produk.'" id="kondisi-'.$row->id_produk.'" style="width:100%;" class="required  select2" onchange="edit_status_barang_retur(this)" >
							<option value="'.$row->kondisi.'">'.$row->kondisi.'</option>
							<option value="BAGUS">BAGUS</option>
							<option value="JELEK">JELEK</option>
						</select></td>';
						
						
						//echo'<td><input style="text-align:right;" type="text" name="harga-'.$row->id_produk.'" id="harga-'.$row->id_produk.'" maxlength="10" size="7" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)" onchange="edit_d_pembelian(this.id)" class="required " value="'.number_format($row->harga,1,'.',',') .'" /></td>';
						
						echo'<input type="hidden" name="diskon-'.$row->id_produk.'" id="diskon-'.$row->id_produk.'" maxlength="10" size="7" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)" onchange="edit_d_pembelian(this.id)" class="required " value="'.$row->diskon.'" />';
						
						echo'<select style="visibility:hidden;" name="optr_diskon-'.$row->id_produk.'" id="optr_diskon-'.$row->id_produk.'" class="required  select2" onchange="edit_d_pembelian(this.id)"><option value="'.$row->optr_diskon.'">'.$row->optr_diskon.'</option><option value="%">%</option><option value="C">C</option></select>';
						
						//echo'<td style="text-align:right;"><span id="spsubtotal-'.$row->id_produk.'">'.number_format($row->subtotal,1,'.',',').'</span><input type="hidden" name="subtotal" id="subtotal-'.$row->id_produk.'" value="'.number_format($row->subtotal,1,'.',',').'" /></td>';
						
						echo'<td><input type="hidden" name="status_konversi" id="status_konversi-'.$row->id_produk.'" value="'.$row->status_konversi.'" /><input type="hidden" name="konversi" id="konversi-'.$row->id_produk.'" value="'.$row->konversi.'" />';
						echo'<input type="hidden" name="harga_dasar-'.$row->id_produk.'" id="harga_dasar-'.$row->id_produk.'" value="'.$row->harga.'" />';
						echo'</td>';
					echo'</tr>';
					$no++;
				}
				
		}
		else
		{
			echo "TIDAK ADA DATA YANG DITAMPILKAN";
		}
	}
	
	function hapus_all()
	{
		$cari = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(id_h_penjualan) = '".$_GET['hapus']."' ";
			
		$data_h_retur = $this->M_gl_h_retur->get_h_retur_by_query($cari);
		if(!empty($data_h_retur))
		{
			$data_h_retur = $data_h_retur->row();
			
			//HAPUS H RETUR
				$this->M_gl_h_retur->hapus_h_retur($cari);
			//HAPUS H RETUR
			
			//HAPUS D RETUR
				$this->M_gl_h_retur->hapus_d_retur($cari);
			//HAPUS D RETUR
		}
		
		//echo"BERHASIL";
		if($_GET['from'] == "laporan")
		{
			header('Location: '.base_url().'gl-admin-laporan-retur');
		}
		else
		{
			if($_GET['status'] == "RETUR-PENJUALAN")
			{
				header('Location: '.base_url().'gl-admin-retur-pelanggan');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-retur-supplier');
			}
		}
	}

	function print_faktur_retur()
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
				//echo "KOSONG";
				//echo $this->uri->segment(2,0);
				if(!empty($this->uri->segment(2,0)))
				{
					$id_h_penjualan = $this->uri->segment(2,0);
					
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_h_penjualan) = '".$id_h_penjualan."' ";
					
					
					$order_by = "ORDER BY A.tgl_ins DESC";
					//$data_h_mutasi = $this->M_gl_h_mutasi->get_h_mutasi_by_query($cari);
					
					$data_h_mutasi = $this->M_gl_h_retur->list_h_retur_supplier($cari,$order_by,1,0);
					
					if(!empty($data_h_mutasi))
					{
						$data_h_mutasi = $data_h_mutasi->row();
						
						/*TAMPILKAN*/
						
						$data = array('page_content'=>'admin/page/gl_admin_print_faktur_retur.html','data_h_mutasi' => $data_h_mutasi);
						$this->load->view('admin/page/gl_admin_print_faktur_retur.html',$data);
						//echo "ADA DATA";
						/*TAMPILKAN*/

					}
					else
					{
						//header('Location: '.base_url().'gl-admin-pendaftaran-pasien');
						//echo $id_h_penjualan;
						//echo "KOSONG";
					}
				}
				else
				{
					//header('Location: '.base_url().'gl-admin-pendaftaran-pasien');
					//echo "KOSONG";
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
/* Location: ./application/controllers/C_gl_admin_mutasi_out.php */