<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_mutasi_out extends CI_Controller {

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
				
				
				$msgbox_title = " Mutasi Out/Pemakaian Produk";
				
				$data = array('page_content'=>'gl_admin_h_mutasi','msgbox_title' => $msgbox_title,'data_h_mutasi' => $data_h_mutasi,'data_kantor' => $data_kantor);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_h_penjualan()
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
					AND A.sts_penjualan IN ('SELESAI','PEMBAYARAN')
					-- AND A.sts_penjualan <> 'SELESAI'
					AND DATE(A.tgl_h_penjualan) BETWEEN '".$dari."' AND '".$sampai."'
					AND 
					(
						A.no_faktur LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						OR COALESCE(D.no_costumer,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(D.nama_lengkap,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(E.nama_karyawan,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
					)";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.sts_penjualan IN ('SELESAI','PEMBAYARAN') 
					-- AND A.sts_penjualan <> 'SELESAI'
					AND DATE(A.tgl_h_penjualan) BETWEEN '".$dari."' AND '".$sampai."' ";
		}
		
		$order_by = "ORDER BY A.tgl_ins DESC";
		
		$list_h_penjualan = $this->M_gl_h_mutasi->list_h_penjualan($cari,$order_by,30,0);
		
		if(!empty($list_h_penjualan))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="35%">FAKTUR</th>';
							echo '<th width="45%">PASIEN</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_h_penjualan->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					if($row->sts_penjualan == "PENDING")
					{
						echo'<tr id="tr_list_penjualan-'.$no.'" style="background-color:yellow;">';
					}
					else
					{
						echo'<tr id="tr_list_penjualan-'.$no.'">';
					}
					
						echo'<td>'.$no.'</td>';
						echo'<td>
								<b>NO FAKTUR : </b>'.$row->no_faktur.' 
								<b>JENIS KUNJUNGAN : </b>'.$row->type_h_penjualan.' 
								<br/> <b>WAKTU : </b>'.$row->DT_PENJUALAN.'
							</td>';
						echo'<td>
								<b>NO PASIEN : </b>'.$row->no_costmer.'
								<br/> <b>NAMA : </b>'.$row->nama_costumer.'
							</td>';
						
						echo'<td>
							<button type="button" onclick="insert_h_penjualan('.$no.')" class="btn btn-success btn-block btn-flat" data-dismiss="modal">PILIH</button>
						</td>';
				
					echo'</tr>';
					
					echo'<input type="hidden" id="id_h_penjualan_5_'.$no.'" name="id_h_penjualan_5_'.$no.'" value="'.$row->id_h_penjualan.'" />';
					echo'<input type="hidden" id="no_faktur_5_'.$no.'" name="no_faktur_5_'.$no.'" value="'.$row->no_faktur.'" />';
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
	
	function list_kantor()
	{	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE 
							kode_kantor <>  '".$this->session->userdata('ses_kode_kantor')."'
							AND nama_kantor LIKE '%".str_replace("'","",$_POST['cari'])."%'
							
					";
		}
		else
		{
			$cari = "WHERE kode_kantor <>  '".$this->session->userdata('ses_kode_kantor')."'";
		}
		
		$list_kantor = $this->M_gl_pengaturan->get_data_kantor($cari);
		
		if(!empty($list_kantor))
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
				$list_result = $list_kantor->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_list_penjualan-'.$no.'">';
						echo'<td>'.$no.'</td>';
						echo'<td>
								<b>KODE : </b>'.$row->kode_kantor.' 
								<br/> <b>NAMA : </b>'.$row->nama_kantor.' 
							</td>';
						echo'<td>
								'.$row->alamat.'
							</td>';
						
						echo'<td>
							<button type="button" onclick="insert_kantor('.$no.')" class="btn btn-success btn-block btn-flat" data-dismiss="modal">PILIH</button>
						</td>';
				
					echo'</tr>';
					
					echo'<input type="hidden" id="id_kantor_6_'.$no.'" name="id_kantor_6_'.$no.'" value="'.$row->id_kantor.'" />';
					echo'<input type="hidden" id="kode_kantor_6_'.$no.'" name="kode_kantor_6_'.$no.'" value="'.$row->kode_kantor.'" />';
					echo'<input type="hidden" id="nama_kantor_6_'.$no.'" name="nama_kantor_6_'.$no.'" value="'.$row->nama_kantor.'" />';
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

	function list_produk()
	{	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.isProduk IN ('PRODUK','CONSUMABLE')
					AND B.besar_konversi = 1
					AND E.id_produk IS NULL
					AND (A.kode_produk LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_produk LIKE '%".str_replace("'","",$_POST['cari'])."%')";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.isProduk IN ('PRODUK','CONSUMABLE') AND B.besar_konversi = 1 AND E.id_produk IS NULL";
		}
		
		$order_by = " ORDER BY A.nama_produk ASC ";
		
		$list_produk = $this->M_gl_produk->list_produk_limit_harga_dasar_untuk_Mutasi_out($_POST['id_h_penjualan'],$cari,$order_by,$_POST['limit'],$_POST['offset']);
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

	function simpan_h_mutasi_awal()
	{
		$get_id_penjualan = $this->M_gl_h_mutasi->get_id_h_penjualan_mutasi($this->session->userdata('ses_kode_kantor'));
		//$get_id_penjualan = $get_id_penjualan->row();
		$id_h_penjualan = $get_id_penjualan->id_h_penjualan;
		
		$get_no_faktur = $this->M_gl_h_mutasi->get_no_faktur_mutasi($_POST['jenis'],$this->session->userdata('ses_kode_kantor'));
		//$get_no_faktur = $get_no_faktur->row();
		$no_faktur = $get_no_faktur->NO_FAKTUR;
		
		
		$this->M_gl_h_mutasi->simpan_h_mutasi
		(
			$id_h_penjualan,
			'', //$id_h_pemesanan,
			'', //$id_h_retur,
			'', //$id_gudang_dari,
			'', //$id_gudang_ke,
			'', //$id_costumer,
			'', //$id_karyawan,
			$_POST['kode_kantor_mutasi'], //$kode_kantor_mutasi,
			$no_faktur,
			$_POST['no_faktur_penjualan'], //$no_faktur_penjualan,
			$_POST['biaya'],
			$_POST['nominal_retur'],
			$_POST['bayar'],
			$_POST['isTunai'],
			$_POST['tgl_pengajuan'],
			$_POST['tgl_h_penjualan'],
			$_POST['tgl_tempo'],
			$_POST['status_penjualan'],
			$_POST['ket_penjualan'],
			$_POST['type_h_penjualan'],
			$_POST['acc_mutasi'],
			$this->session->userdata('ses_id_karyawan'), //$_POST['user_updt'],
			$this->session->userdata('ses_kode_kantor'), //$_POST['kode_kantor'],
			$_POST['sts_penjualan']
		);
		
		
		echo $id_h_penjualan.'-'.$no_faktur;
	}
	
	function simpan_d_mutasi()
	{
		$this->M_gl_h_mutasi->simpan_d_mutasi
		(
			$_POST['id_h_penjualan'],
			'', //$_POST['id_d_penerimaan'],
			$_POST['id_produk'],
			$_POST['jumlah_req'],
			$_POST['jumlah'],
			$_POST['status_konversi'],
			$_POST['konversi'],
			$_POST['satuan_jual'],
			$_POST['jumlah_konversi'],
			$_POST['harga'],
			$_POST['harga_konversi'],
			$_POST['harga_ori'],
			'', //$_POST['ket_d_penjualan'],
			$this->session->userdata('ses_id_karyawan'),
			$this->session->userdata('ses_kode_kantor')
		);
	}
	
	function ubah_d_mutasi()
	{
		$cari = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' AND id_produk = '".$_POST['id_produk']."'";
			
			$data_d_mutasi = $this->M_gl_h_mutasi->get_d_mutasi_by_query($cari);
			if(!empty($data_d_mutasi))
			{
				$data_d_mutasi = $data_d_mutasi->row();
				
				if(str_replace(",","",$_POST['jumlah']) > 0)
				{
					$this->M_gl_h_mutasi->ubah_d_mutasi
					(
						$_POST['id_h_penjualan'],
						$_POST['id_produk'],
						$_POST['jumlah_req'],
						$_POST['jumlah'],
						$_POST['status_konversi'],
						$_POST['konversi'],
						$_POST['satuan_jual'],
						$_POST['jumlah_konversi'],
						$_POST['harga'],
						$_POST['harga_konversi'],
						$_POST['harga_ori'],
						$_POST['ket_d_penjualan'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				else
				{
					$cari = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_d_penjualan = '".$data_d_mutasi->id_d_penjualan."'";
					$this->M_gl_h_mutasi->hapus_d_mutasi($cari);
				}
			}
			
		
	}

	function batal()
	{
		$cari = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' ";
			
		$data_h_mutasi = $this->M_gl_h_mutasi->get_h_mutasi_by_query($cari);
		if(!empty($data_h_mutasi))
		{
			$data_h_mutasi = $data_h_mutasi->row();
			
			if($data_h_mutasi->sts_penjualan == 'PENDING')
			{
				//HAPUS H MUTASI
					$this->M_gl_h_mutasi->hapus_h_mutasi($cari);
				//HAPUS H MUTASI
				
				//HAPUS D MUTASI
					$this->M_gl_h_mutasi->hapus_d_mutasi($cari);
				//HAPUS D MUTASI
			}
			/*
			else
			{
				$this->M_gl_h_mutasi->ubah_h_mutasi
				(
					$_POST['id_h_penjualan'], //$id_h_penjualan,
					$data_h_mutasi->id_h_pemesanan,
					$data_h_mutasi->id_h_retur,
					$data_h_mutasi->id_gudang_dari,
					$data_h_mutasi->id_gudang_ke,
					$data_h_mutasi->id_costumer,
					$data_h_mutasi->id_karyawan,
					$data_h_mutasi->kode_kantor_mutasi,
					$data_h_mutasi->no_faktur,
					$data_h_mutasi->no_faktur_penjualan,
					$data_h_mutasi->biaya,
					$data_h_mutasi->nominal_retur,
					$data_h_mutasi->bayar,
					$data_h_mutasi->isTunai,
					$data_h_mutasi->tgl_pengajuan,
					$data_h_mutasi->tgl_h_penjualan,
					$data_h_mutasi->tgl_tempo,
					$data_h_mutasi->status_penjualan,
					$data_h_mutasi->ket_penjualan,
					$data_h_mutasi->type_h_penjualan,
					$data_h_mutasi->acc_mutasi,
					$this->session->userdata('ses_id_karyawan'),
					$data_h_mutasi->kode_kantor,
					$data_h_mutasi->sts_penjualan

				);
			}
			*/
		}
		
		echo"BERHASIL";
	}

	function simpan_selesai()
	{
		$cari = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' ";
			
		$data_h_mutasi = $this->M_gl_h_mutasi->get_h_mutasi_by_query($cari);
		if(!empty($data_h_mutasi))
		{
			$data_h_mutasi = $data_h_mutasi->row();
			
			$this->M_gl_h_mutasi->ubah_h_mutasi
			(
				$_POST['id_h_penjualan'], //$id_h_penjualan,
				$data_h_mutasi->id_h_pemesanan,
				$data_h_mutasi->id_h_retur,
				$data_h_mutasi->id_gudang_dari,
				$data_h_mutasi->id_gudang_ke,
				$data_h_mutasi->id_costumer,
				$data_h_mutasi->id_karyawan,
				$_POST['kode_kantor'], //$data_h_mutasi->kode_kantor_mutasi,
				$data_h_mutasi->no_faktur,
				$_POST['no_faktur_penjualan'], //$data_h_mutasi->no_faktur_penjualan,
				$data_h_mutasi->biaya,
				$data_h_mutasi->nominal_retur,
				$data_h_mutasi->bayar,
				$data_h_mutasi->isTunai,
				$_POST['tgl_h_penjualan'], //$data_h_mutasi->tgl_pengajuan,
				$_POST['tgl_h_penjualan'], //$data_h_mutasi->tgl_h_penjualan,
				$data_h_mutasi->tgl_tempo,
				$data_h_mutasi->status_penjualan,
				$_POST['ket_penjualan'], //$data_h_mutasi->ket_penjualan,
				$data_h_mutasi->type_h_penjualan,
				$data_h_mutasi->acc_mutasi,
				$this->session->userdata('ses_id_karyawan'),
				$data_h_mutasi->kode_kantor,
				"SELESAI", //$data_h_mutasi->sts_penjualan
				$_POST['forTindakan']

			);
		}
	}

	function print_faktur_mutasi_out()
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
					$data_h_mutasi = $this->M_gl_h_mutasi->get_h_mutasi_by_query($cari);
					if(!empty($data_h_mutasi))
					{
						$data_h_mutasi = $data_h_mutasi->row();
						
						/*TAMPILKAN*/
						
						$data = array('page_content'=>'admin/page/gl_admin_print_faktur_mutasi_out.html','data_h_mutasi' => $data_h_mutasi);
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
					header('Location: '.base_url().'gl-admin-pendaftaran-pasien');
				}
				
				
				
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
				AND A.status_penjualan = 'MUTASI-OUT'
				AND DATE(A.tgl_h_penjualan) = DATE(NOW())
				AND (A.no_faktur LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.ket_penjualan LIKE '%".str_replace("'","",$_POST['cari'])."%')
			";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
			AND A.sts_penjualan = 'SELESAI'
			AND A.status_penjualan = 'MUTASI-OUT'
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
						
						echo'<td>
								<b>KANTOR : </b>'.$row->nama_kantor.'
								<br/><b>KUNJUNGAN : </b>'.$row->no_faktur_penjualan.'
								<br/><b>KETERANGAN : </b>'.$row->ket_penjualan.' 
							</td>';
						
						/*DENHGAN ENRYPTION */
						echo'<td>
								<button type="button" id="'.$row->id_h_penjualan.'" onclick="cetak_ulang_faktur(this)" class="btn btn-primary btn-block btn-flat">PRINT</button>
								
								<a href="'.base_url().'gl-admin-mutasi-out/'.(md5($row->id_h_penjualan)).'" class="confirm-btn btn btn-success btn-block btn-flat" title="Apakah yakin akan mengubah mutasi out '.$row->no_faktur.' ?" alt="Apakah yakin akan mengubah mutasi out '.$row->no_faktur.' ?">UBAH</a>
								
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

	function list_d_mutasi()
	{	
		
		$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_h_penjualan = '".$_POST['id_h_penjualan']."'";
		
		$order_by = " ORDER BY A.tgl_ins DESC ";
		
		$list_d_mutasi = $this->M_gl_h_mutasi->list_d_mutasi_produk($cari,$order_by,1000,0);
		
		if(!empty($list_d_mutasi))
		{
				$list_result = $list_d_mutasi->result();
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
						
						
						//echo'<td><input style="text-align:right;" type="text" name="harga-'.$row->id_produk.'" id="harga-'.$row->id_produk.'" maxlength="10" size="7" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)" onchange="edit_d_pembelian(this.id)" class="required " value="'.number_format($row->harga,1,'.',',') .'" /></td>';
						
						//echo'<td><input type="text" name="diskon-'.$row->id_produk.'" id="diskon-'.$row->id_produk.'" maxlength="10" size="7" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)" onchange="edit_d_pembelian(this.id)" class="required " value="'.$row->diskon.'" /></td>';
						
						//echo'<td><select name="optr_diskon-'.$row->id_produk.'" id="optr_diskon-'.$row->id_produk.'" class="required  select2" onchange="edit_d_pembelian(this.id)"><option value="'.$row->optr_diskon.'">'.$row->optr_diskon.'</option><option value="%">%</option><option value="C">C</option></select></td>';
						
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
			
		$data_h_mutasi = $this->M_gl_h_mutasi->get_h_mutasi_by_query($cari);
		if(!empty($data_h_mutasi))
		{
			$data_h_mutasi = $data_h_mutasi->row();
			
			//HAPUS H MUTASI
				$this->M_gl_h_mutasi->hapus_h_mutasi($cari);
			//HAPUS H MUTASI
			
			//HAPUS D MUTASI
				$this->M_gl_h_mutasi->hapus_d_mutasi($cari);
			//HAPUS D MUTASI
		}
		
		//echo"BERHASIL";
		header('Location: '.base_url().'gl-admin-mutasi-out');
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_mutasi_out.php */