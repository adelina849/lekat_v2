<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_pst_admin_h_pembelian extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_h_pembelian','M_gl_d_pembelian','M_gl_produk','M_gl_satuan_konversi','M_gl_supplier','M_gl_bank','M_gl_d_pembelian_bayar','M_gl_kat_produk'));
		
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
					$id_h_pembelian = $this->uri->segment(2,0);
					$data_h_pembelian = $this->M_gl_h_pembelian->get_h_pembelian('id_h_pembelian',$id_h_pembelian);
					if(!empty($id_h_pembelian))
					{
						$data_h_pembelian = $data_h_pembelian->row();
						
						$data_supplier = $this->M_gl_supplier->get_supplier('id_supplier',$data_h_pembelian->id_supplier);
						if(!empty($data_supplier))
						{
							$data_supplier = $data_supplier->row();
						}
						else
						{
							$data_supplier = false;
						}
						
						$cari_bayar_cash = " WHERE A.id_h_pembelian = '".$id_h_pembelian."' AND A.cara = 'CASH' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
						$order_by_bayar_cash = " ORDER BY A.tgl_ins ASC";
						$data_pembayaran_cash = $this->M_gl_d_pembelian_bayar->list_d_pembelian_bayar($cari_bayar_cash,$order_by_bayar_cash,1,0);
						if(!empty($data_pembayaran_cash))
						{
							$data_pembayaran_cash = $data_pembayaran_cash->row();
						}
						else
						{
							$data_pembayaran_cash = false;
						}
						
						$cari_bayar_tf = " WHERE A.id_h_pembelian = '".$id_h_pembelian."' AND A.cara <> 'CASH' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
						$order_by_bayar_tf = " ORDER BY A.tgl_ins ASC";
						$data_pembayaran_tf = $this->M_gl_d_pembelian_bayar->list_d_pembelian_bayar($cari_bayar_tf,$order_by_bayar_tf,1,0);
						if(!empty($data_pembayaran_tf))
						{
							$data_pembayaran_tf = $data_pembayaran_tf->row();
						}
						else
						{
							$data_pembayaran_tf = false;
						}
					}
				}
				else
				{
					$data_h_pembelian = false;
					$data_supplier = false;
					$data_pembayaran_cash = false;
					$data_pembayaran_tf = false;
				}
				
				$cari_bank = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
				$list_bank = $this->M_gl_bank->list_bank_limit($cari_bank,30,0);
				
				$cari_list_kat_produk = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND isProduk <> 'JASA'";
				$list_kat_produk = $this->M_gl_kat_produk->list_kat_produk_limit($cari_list_kat_produk,1000,0);
				
				$msgbox_title = " Purchase Order";
				
				$data = array('page_content'=>'gl_admin_h_pembelian','msgbox_title' => $msgbox_title,'data_h_pembelian' => $data_h_pembelian,'data_supplier' => $data_supplier,'list_bank' => $list_bank,'data_pembayaran_tf' => $data_pembayaran_tf,'data_pembayaran_cash' => $data_pembayaran_cash,'list_kat_produk' => $list_kat_produk);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_h_pembelian_penerimaan()
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
				if((!empty($_GET['id_supplier'])) && ($_GET['id_supplier']!= "")  )
				{
					$id_supplier = $_GET['id_supplier'];
				}
				else
				{
					$id_supplier = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = $_GET['cari'];
				}
				else
				{
					$cari = "";
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
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				//UNTUK AKUMULASI INFO
					$jum_row = $this->M_gl_h_pembelian->count_h_pembelian_penerimaan($this->session->userdata('ses_kode_kantor'),$cari,$dari,$sampai)->JUMLAH;
				//UNTUK AKUMULASI INFO
				
				$config['first_url'] = site_url('gl-pusat-admin-purchase-order-terima?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-pusat-admin-purchase-order-terima/');
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
				
				$order_by = " ORDER BY A.tgl_ins DESC";
				$list_h_pembelian_penerimaan = $this->M_gl_h_pembelian->list_h_pembelian_penerimaan($this->session->userdata('ses_kode_kantor'),$id_supplier,$cari,$dari,$sampai,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				$list_data_supplier = $this->M_gl_supplier->list_supplier_limit(" WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'",1000,0);
				
				$msgbox_title = " Purchase Order";
				
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
				
				$data = array(
						'page_content'=>'gl_pusat_admin_h_pembelian_penerimaan',
						'halaman'=>$halaman,
						'list_h_pembelian_penerimaan'=>$list_h_pembelian_penerimaan,
						'msgbox_title' => $msgbox_title,
						'sum_pesan' => $sum_pesan,
						'list_data_supplier' => $list_data_supplier,

					);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function view_modal_d_pembelian()
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
				if((!empty($_POST['kode_kantor'])) && ($_POST['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_POST['kode_kantor']);
				}
				else
				{
					$kode_kantor = "";
				}
				
				
				
				$cari = 
				"
					WHERE A.kode_kantor = '".$kode_kantor."' AND A.id_h_pembelian = '".$_POST['id_h_pembelian']."'
				";
				
				$order_by = "ORDER BY COALESCE(B.nama_produk,'') ASC";
				
				$list_view_d_pembelian = $this->M_gl_d_pembelian->list_d_pembelian($cari,$order_by,10000,0);
				
				echo'<div class="box-body table-responsive no-padding">';
					if(!empty($list_view_d_pembelian))
					{
						echo'<table width="100%" id="example2" class="table table-hover">';
							echo '<thead>
<tr>';
										echo '<th width="5%">NO</th>';
										echo '<th width="15%">KODE</th>';
										echo '<th width="30%">NAMA PRODUK</th>';
										echo '<th width="10%">BANYAK</th>';
										echo '<th width="10%">HARGA</th>';
										echo '<th width="10%">DISKON</th>';
										echo '<th width="10%">SUBTOTAL</th>';
										//echo '<th width="15%">CABANG</th>';
							echo '</tr>
</thead>';
							$list_result = $list_view_d_pembelian->result();
							$no =1;
							$total = 0;
							echo '<tbody>';
							foreach($list_result as $row)
							{
								echo'<tr>';
									echo'<td>'.$no.'</td>';
									
									echo '<td>'.$row->kode_produk.'</td>';
									echo '<td>'.$row->nama_produk.'</td>';
									echo '<td>'.number_format($row->jumlah,0,'.',',').'</td>';
									
									/*
									if($row->besar_diskon > 0)
									{
										echo '<td>'.number_format($row->harga,0,'.',',').' <br/> <b>Diskon : </b>'.number_format($row->besar_diskon,0,'.',',').'</td>';
									}
									else
									{
										echo '<td>'.number_format($row->harga,0,'.',',').'</td>';
									}
									*/
									echo '<td>'.number_format($row->harga,0,'.',',').'</td>';
									echo '<td>'.number_format($row->besar_diskon,0,'.',',').'</td>';
									
									echo '<td>'.number_format($row->subtotal,0,'.',',').'</td>';
									
									
									/*
									echo '<td>
										<a href="javascript:void(0)" class="btn btn-success btn-sm btn-flat" data-toggle="modal" data-target="#myModal_pengeluaran_produk" id="DET_KELUAR-'.$row->id_produk.'"  onclick="tampilkan_detail_produk_by_kategori_by_faktur(this)" title = "Detail Pengeluaran '.$row->nama_produk.'" alt = "Detail Pengeluaran '.$row->nama_produk.'">DETAIL</a>
									</td>';
									*/
									//echo '<td>'.$row->kode_kantor.'</td>';
									
									
									//echo '<th width="10%" style="text-align:right;">'.number_format($grandTotal,0,'.',',').'</th>';
									
									$total = $total + $row->subtotal;
								echo'</tr>';
								
								
								
								
								$no++;
							}
							echo'
								<tr>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td><b>TOTAL</b></td>
									<td><b>'.number_format($total,0,'.',',').'</b></td>
								</tr>';
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
			else
			{
				header('Location: '.base_url());
			}
		}
	}
	
	function list_tgl_expired()
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
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					if($this->session->userdata('ses_kode_kantor') == 'PST')
					{
						$kode_kantor = "";
					}
					else
					{
						$kode_kantor = $this->session->userdata('ses_kode_kantor');
					}
					
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = $_GET['cari'];
				}
				else
				{
					$cari = "";
				}
				
				
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
				$list_h_pembelian_penerimaan_tgl_exp = $this->M_gl_h_pembelian->list_tgl_expired($kode_kantor,$cari);
				
				$msgbox_title = " Informasi Tanggal Expired Produk";
				
				$data = array('page_content'=>'gl_admin_h_pembelian_tgl_exp','list_h_pembelian_penerimaan_tgl_exp'=>$list_h_pembelian_penerimaan_tgl_exp,'msgbox_title' => $msgbox_title,'list_kantor' => $list_kantor);
				$this->load->view('pusat/container',$data);
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
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.isProduk IN ('PRODUK','CONSUMABLE')
					AND B.besar_konversi = 1
					AND E.id_produk IS NULL
					AND COALESCE(F.id_kat_produk,'') LIKE '%".str_replace("'","",$_POST['id_kat_produk'])."%'
					AND (A.kode_produk LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_produk LIKE '%".str_replace("'","",$_POST['cari'])."%')";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.isProduk IN ('PRODUK','CONSUMABLE') AND B.besar_konversi = 1 AND E.id_produk IS NULL AND COALESCE(F.id_kat_produk,'') LIKE '%".str_replace("'","",$_POST['id_kat_produk'])."%'  ";
		}
		
		$order_by = " ORDER BY A.nama_produk ASC ";
		
		if($this->session->userdata('ses_kode_kantor') == 'PST')
		{
			$list_produk = $this->M_gl_produk->list_produk_limit_harga_dasar_untuk_PO_ori_harga_dasar($_POST['id_h_pembelian'],$cari,$order_by,$_POST['limit'],$_POST['offset']);
			//($cari,$_POST['limit'],$_POST['offset']);
		}
		else
		{
			$list_produk = $this->M_gl_produk->list_produk_limit_harga_dasar_untuk_PO($_POST['id_h_pembelian'],$cari,$order_by,$_POST['limit'],$_POST['offset']);
			//($cari,$_POST['limit'],$_POST['offset']);
		}
			
		
		
		if(!empty($list_produk))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="5%">PILIH</th>';
							echo '<th width="10%">FOTO</th>';
							echo '<th width="50%">DATA PRODUK</th>';
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
						
						if($row->isProduk == 'CONSUMABLE')
						{
							echo'<td>
								<b>KODE : </b>'.$row->kode_produk.' 
								<br/> <b>NAMA : </b>'.$row->nama_produk.' 
								<br/> <b>HARGA : </b><b style="color:red;">'.number_format($row->harga_jual,2,'.',',').' /'.$row->kode_satuan.'</b>
								<br/> <b>JENIS : </b><font style="color:blue;">'.$row->isProduk.'</font> 
							</td>';
						}
						else
						{
							echo'<td>
								<b>KODE : </b>'.$row->kode_produk.' 
								<br/> <b>NAMA : </b>'.$row->nama_produk.' 
								<br/> <b>HARGA : </b><b style="color:red;">'.number_format($row->harga_jual,2,'.',',').' /'.$row->kode_satuan.'</b>
								<br/> <b>JENIS : </b>'.$row->isProduk.' 
							</td>';
						}
						
						echo'<td>
								<!-- <button type="button" onclick="insert_produk('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button> -->
								<button type="button" onclick="insert_produk('.$no.')" id="btn_pilih_'.$no.'" class="btn btn-primary btn-sm" >Pilih</button>
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
	
	function simpan_h_pembelian_awal()
	{
		$get_id_pembelian = $this->M_gl_h_pembelian->get_id_h_pembelian_dan_faktur($this->session->userdata('ses_kode_kantor'));
		$id_h_pembelian = $get_id_pembelian->id_h_pembelian;
		$no_h_pembelian = $get_id_pembelian->NO_FAKTUR;
		
		$this->M_gl_h_pembelian->simpan
		(
			$id_h_pembelian,
			$_POST['id_supplier'],
			'', //$id_h_retur,
			$no_h_pembelian,
			'', //$nama_h_pembelian,
			$_POST['tgl_h_pembelian'],
			'', //$tgl_jatuh_tempo,
			0, //$nominal_transaksi,
			0, //$nominal_retur,
			0, //$bayar_detail,
			0, //$biaya_tambahan,
			0, //$pengurang,
			0, //$ket_h_pembelian,
			'PENDING', //$sts_pembelian,
			$this->session->userdata('ses_id_karyawan'),
			$this->session->userdata('ses_kode_kantor')
		);
		
		echo $id_h_pembelian.'-'.$no_h_pembelian;
	}
	
	function simpan_d_pembelian_awal()
	{
		$this->M_gl_d_pembelian->simpan
		(
			$_POST['id_h_pembelian'],
			$_POST['id_produk'],
			$_POST['jumlah'],
			$_POST['harga'],
			$_POST['harga_dasar'],
			$_POST['diskon'],
			$_POST['optr_diskon'],
			$_POST['kode_satuan'],
			$_POST['nama_satuan'],
			$_POST['status_konversi'],
			$_POST['konversi'],
			'1', //$acc,
			$this->session->userdata('ses_id_karyawan'),
			$this->session->userdata('ses_kode_kantor')
		);
	}
	
	function combo_list_satuan()
	{
		$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
				AND A.id_produk = '".$_POST['id_produk']."'";
		$order_by = " ORDER BY COALESCE(B.kode_satuan,'') ASC";
		
		$list_satuan = $this->M_gl_satuan_konversi->list_data_satuan_konversi($cari,$order_by,0,30);
		if(!empty($list_satuan))
		{
			$list_result = $list_satuan->result();
			foreach($list_result as $row)
			{
				echo '<option value="'.$row->kode_satuan.'">'.$row->kode_satuan.'</option>';
			}
		}
	}
	
	function combo_satuan_harga()
	{
		$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
				AND A.id_produk = '".$_POST['id_produk']."' AND COALESCE(B.kode_satuan,'') = '".$_POST['kode_satuan']."'";
		$order_by = " ORDER BY COALESCE(B.kode_satuan,'') ASC";
		
		$list_satuan = $this->M_gl_satuan_konversi->list_data_satuan_konversi($cari,$order_by,0,30);
		if(!empty($list_satuan))
		{
			$list_satuan = $list_satuan->row();
			echo $list_satuan->harga_jual.'|'.$list_satuan->status.'|'.$list_satuan->besar_konversi.'|'.$list_satuan->harga_jual;
		}
		else
		{
			echo 0;
		}
		
	}
	
	function ubah_d_pembelian()
	{
		/*HARGA DASAR JANGAN IKUT DIUBAH, KARENA HANYA BERUBAH HARGA ATAU JUMLAHTIDAK AKAN BERPENGARUJ KEPADA HARGRA DASAR*/
			$cari = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_pembelian = '".$_POST['id_h_pembelian']."' AND id_produk = '".$_POST['id_produk']."'";
			
			$data_d_pembelian = $this->M_gl_d_pembelian->get_d_pembelian_by_query($cari);
			
			if(!empty($data_d_pembelian))
			{
				$data_d_pembelian = $data_d_pembelian->row();
				
				if(str_replace(",","",$_POST['jumlah']) > 0)
				{
					$this->M_gl_d_pembelian->edit_with_ket_d_penjualan
					(
						$data_d_pembelian->id_d_pembelian,
						$_POST['id_h_pembelian'],
						$_POST['id_produk'],
						str_replace(",","",$_POST['jumlah']) , //$_POST['jumlah'],
						str_replace(",","",$_POST['harga']) , //$_POST['harga'],
						str_replace(",","",$_POST['harga_dasar']) , //$_POST['harga_dasar]',
						str_replace(",","",$_POST['diskon']) , //$_POST['diskon'],
						$_POST['optr_diskon'],
						$_POST['kode_satuan'],
						$_POST['nama_satuan'],
						$_POST['status_konversi'],
						$_POST['konversi'],
						$data_d_pembelian->acc,
						$_POST['ket_d_penjualan'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				else
				{
					$cari = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_d_pembelian = '".$data_d_pembelian->id_d_pembelian."'";
					$this->M_gl_d_pembelian->hapus($cari);
				}
			}
		/*HARGA DASAR JANGAN IKUT DIUBAH, KARENA HANYA BERUBAH HARGA ATAU JUMLAHTIDAK AKAN BERPENGARUJ KEPADA HARGRA DASAR*/
	}
	
	function terbilang()
	{
		echo '<i style="color:red;">'.strtoupper(Terbilang($_POST['nominal'])).'</i>';
	}
	
	
	function list_supplier()
	{	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (A.kode_supplier LIKE '%".str_replace("'","",$_POST['cari'])."%' OR A.nama_supplier LIKE '%".str_replace("'","",$_POST['cari'])."%')
							";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
		}
		
		$list_supplier = $this->M_gl_supplier->list_supplier_limit($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_supplier))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="30%">NAMA</th>';
							echo '<th width="50%">ALAMAT</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_supplier->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_produk-'.$no.'">';
					
						echo'<td>'.$no.'</td>';
						echo'<td>
								<b>KODE : </b>'.$row->kode_supplier.' 
								<br/> <b>NAMA : </b>'.$row->nama_supplier.' 
							</td>';
						echo'<td>
								<b>TELPON : </b>'.$row->tlp.' 
								<br/> <b>EMAIL : </b>'.$row->email.' 
								<br/> <b>ALAMAT : </b>'.$row->alamat.' 
							</td>';
						
						echo'<td>
								<button type="button" onclick="insert_supplier('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button>
							</td>';
							
						echo'<input type="hidden" id="get_tr_4_'.$no.'" name="get_tr_4_'.$no.'" value="tr_list_supplier-'.$no.'" />';
						echo'<input type="hidden" id="id_supplier_4_'.$no.'" name="id_supplier_4_'.$no.'" value="'.$row->id_supplier.'" />';
						echo'<input type="hidden" id="kode_supplier_4_'.$no.'" name="kode_supplier_4_'.$no.'" value="'.$row->kode_supplier.'" />';
						echo'<input type="hidden" id="nama_supplier_4_'.$no.'" name="nama_supplier_4_'.$no.'" value="'.$row->nama_supplier.'" />';
						echo'<input type="hidden" id="tlp_4_'.$no.'" name="tlp_4_'.$no.'" value="'.$row->tlp.'" />';
						echo'<input type="hidden" id="email_4_'.$no.'" name="email_4_'.$no.'" value="'.$row->email.'" />';
						echo'<input type="hidden" id="alamat_4_'.$no.'" name="alamat_4_'.$no.'" value="'.$row->alamat.'" />';
						
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
	
	function ubah_h_pembelian_awal()
	{
		$data_h_pembelian = $this->M_gl_h_pembelian->get_h_pembelian('id_h_pembelian',$_POST['id_h_pembelian']);
		if(!empty($data_h_pembelian))
		{
			$data_h_pembelian = $data_h_pembelian->row();
			if($_POST['sts_pembelian'] == "")
			{
				$sts_pembelian = $data_h_pembelian->sts_pembelian;
			}
			else
			{
				$sts_pembelian = $_POST['sts_pembelian'];
			}
			
			if($_POST['id_h_retur'] == "")
			{
				$id_h_retur = $data_h_pembelian->id_h_retur;
			}
			else
			{
				$id_h_retur = $_POST['id_h_retur'];
			}
			
			if($_POST['nama_h_pembelian'] == "")
			{
				$nama_h_pembelian = $data_h_pembelian->nama_h_pembelian;
			}
			else
			{
				$nama_h_pembelian = $_POST['nama_h_pembelian'];
			}
			
			$this->M_gl_h_pembelian->edit
			(
				$_POST['id_h_pembelian'],
				$_POST['id_supplier'],
				$id_h_retur, //$_POST['id_h_retur'],
				$_POST['no_h_pembelian'],
				$nama_h_pembelian, //$_POST['nama_h_pembelian'],
				$_POST['tgl_h_pembelian'],
				$_POST['tgl_jatuh_tempo'],
				str_replace(",","",$_POST['nominal_transaksi']) , //$_POST['nominal_transaksi'],
				str_replace(",","",$_POST['nominal_retur']) , //$_POST['nominal_retur'],
				str_replace(",","",$_POST['bayar_detail']) , //$_POST['bayar_detail'],
				str_replace(",","",$_POST['biaya_tambahan']) , //$_POST['biaya_tambahan'],
				str_replace(",","",$_POST['pengurang']) , //$_POST['pengurang'],
				$_POST['ket_h_pembelian'],
				$sts_pembelian, //$_POST['sts_pembelian'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			
			echo "BERHASIL";
			//echo $id_h_pembelian.'-'.$no_h_pembelian;
		}
		else
		{
			echo "KOSONG";
		}
		
		
	}
	
	function list_d_pembelian()
	{	
		
		$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_pembelian = '".$_POST['id_h_pembelian']."'";
		
		$order_by = " ORDER BY A.tgl_ins DESC ";
		
		$list_d_pembelian = $this->M_gl_d_pembelian->list_d_pembelian($cari,$order_by,500,0);
		
		if(!empty($list_d_pembelian))
		{
				$list_result = $list_d_pembelian->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_transaksi-'.$row->id_produk.'">';
					
						echo'<td>'.$row->kode_produk.'</td>';
						echo'<td>'.$row->nama_produk.'</td>';
						echo'<td><input type="text" name="jumlah-'.$row->id_produk.'" id="jumlah-'.$row->id_produk.'" maxlength="4" size="4" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)" onchange="edit_d_pembelian(this.id)" class="required " value="'.$row->jumlah.'" /></td>';
						echo'<td><select name="kode_satuan-'.$row->id_produk.'" id="kode_satuan-'.$row->id_produk.'" class="required  select2" onfocus="tampilkan_list_satuan(this.id)" onchange="harga_satuan(this.id)"><option value="'.$row->kode_satuan.'">'.$row->kode_satuan.'</option></select></td>';
						echo'<td><input style="text-align:right;" type="text" name="harga-'.$row->id_produk.'" id="harga-'.$row->id_produk.'" maxlength="10" size="7" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)" onchange="edit_d_pembelian(this.id)" class="required " value="'.number_format($row->harga,1,'.',',') .'" /></td>';
						echo'<td>
						
						<input type="text" name="diskon-'.$row->id_produk.'" id="diskon-'.$row->id_produk.'" maxlength="10" size="7" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)" onchange="edit_d_pembelian(this.id)" class="required " value="'.$row->diskon.'" />
						</td>';
						
						echo'<td><select name="optr_diskon-'.$row->id_produk.'" id="optr_diskon-'.$row->id_produk.'" class="required  select2" onchange="edit_d_pembelian(this.id)"><option value="'.$row->optr_diskon.'">'.$row->optr_diskon.'</option><option value="%">%</option><option value="C">C</option></select></td>';
						echo'<td style="text-align:right;"><span id="spsubtotal-'.$row->id_produk.'">'.number_format($row->subtotal,1,'.',',').'</span><input type="hidden" name="subtotal" id="subtotal-'.$row->id_produk.'" value="'.number_format($row->subtotal,1,'.',',').'" /></td>';
						echo'<td>
						
						<input type="text" name="ket_d_penjualan" id="ket_d_penjualan-'.$row->id_produk.'" onchange="edit_d_pembelian(this.id)"  value="'.$row->ket_d_penjualan.'" />
						
						<input type="hidden" name="status_konversi" id="status_konversi-'.$row->id_produk.'" value="'.$row->status_konversi.'" /><input type="hidden" name="konversi" id="konversi-'.$row->id_produk.'" value="'.$row->konversi.'" />';
						echo'<input type="hidden" name="harga_dasar-'.$row->id_produk.'" id="harga_dasar-'.$row->id_produk.'" value="'.$row->harga_dasar.'" />';
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
	
	function simpan_d_pembelian_bayar_awal()
	{
		$this->M_gl_d_pembelian_bayar->simpan
		(
			
			$_POST['id_h_pembelian'],
			$_POST['id_supplier'],
			$_POST['id_bank'],
			$_POST['id_retur_pembelian'],
			$_POST['cara'],
			$_POST['norek'],
			$_POST['nama_bank'],
			$_POST['atas_nama'],
			$_POST['nominal'],
			$_POST['ket'],
			$_POST['type_bayar'],
			$_POST['id_pembayaran_supplier'],
			$_POST['isTabungan'],
			$_POST['tgl_bayar'],
			$this->session->userdata('ses_id_karyawan'),
			$this->session->userdata('ses_kode_kantor')
		);
		
		echo "BERHASIL";
	}
	
	function ubah_d_pembelian_bayar_awal()
	{
		
		$this->M_gl_d_pembelian_bayar->edit
		(
			$_POST['id_d_bayar'],
			$_POST['id_h_pembelian'],
			$_POST['id_supplier'],
			$_POST['id_bank'],
			$_POST['id_retur_pembelian'],
			$_POST['cara'],
			$_POST['norek'],
			$_POST['nama_bank'],
			$_POST['atas_nama'],
			$_POST['nominal'],
			$_POST['ket'],
			$_POST['type_bayar'],
			$_POST['id_pembayaran_supplier'],
			$_POST['isTabungan'],
			$_POST['tgl_bayar'],
			$this->session->userdata('ses_id_karyawan'),
			$this->session->userdata('ses_kode_kantor')
		);
		
		
		echo "BERHASIL";
	}

	function batal()
	{
		$data_h_pembelian = $this->M_gl_h_pembelian->get_h_pembelian('id_h_pembelian',$_POST['id_h_pembelian']);
		if(!empty($data_h_pembelian))
		{
			$data_h_pembelian = $data_h_pembelian->row();
			if($data_h_pembelian->sts_pembelian == "PENDING")
			{
				$cari = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_pembelian = '".$_POST['id_h_pembelian']."'";
				$this->M_gl_d_pembelian_bayar->hapus($cari);
				$this->M_gl_d_pembelian->hapus($cari);
				$this->M_gl_h_pembelian->hapus('id_h_pembelian',$_POST['id_h_pembelian']);
				echo"BERHASIL";
			}
		}
	}
	
	function hapus_all()
	{
		$data_h_pembelian = $this->M_gl_h_pembelian->get_h_pembelian('id_h_pembelian',$_POST['id_h_pembelian']);
		if(!empty($data_h_pembelian))
		{
			$data_h_pembelian = $data_h_pembelian->row();
			$cari = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_pembelian = '".$_POST['id_h_pembelian']."'";
			$this->M_gl_d_pembelian_bayar->hapus($cari);
			$this->M_gl_d_pembelian->hapus($cari);
			$this->M_gl_h_pembelian->hapus('id_h_pembelian',$_POST['id_h_pembelian']);
			
			echo"BERHASIL";
			
		}
	}

	function list_h_pembelian()
	{	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							AND DATE(A.tgl_h_pembelian) = DATE(NOW()) 
							AND (A.no_h_pembelian LIKE '%".str_replace("'","",$_POST['cari'])."%' OR B.kode_supplier LIKE '%".str_replace("'","",$_POST['cari'])."%' OR B.nama_supplier LIKE '%".str_replace("'","",$_POST['cari'])."%')
							";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND DATE(A.tgl_h_pembelian) = DATE(NOW()) ";
		}
		
		$order_by = " ORDER BY A.tgl_ins DESC ";
		$list_h_pembelian = $this->M_gl_h_pembelian->list_h_pembelian($cari,$order_by,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_h_pembelian))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="35%">PEMBELIAN</th>';
							echo '<th width="45%">SUPPLIER</th>';
							echo '<th width="15%">AKSI</th>';
				echo '</tr>
</thead>';
				$list_result = $list_h_pembelian->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					if($row->sts_pembelian == "PENDING")
					{
						echo'<tr id="tr_list_pembelian-'.$no.'" style="background-color:yellow;">';
					}
					else
					{
						echo'<tr id="tr_list_pembelian-'.$no.'">';
					}
					
						echo'<td>'.$no.'</td>';
						echo'<td>
								<b>NO PO : </b>'.$row->no_h_pembelian.' 
								<br/> <b>NOMINAL : </b>'.number_format($row->nominal_transaksi,2,'.',',').' 
								<br/> <b>(</b>'.$row->tgl_ins.') 
								
							</td>';
						echo'<td>
								<b>SUPPLIER : </b>'.$row->kode_supplier.' ('.$row->kode_supplier.' )
								<br/> <b>EMAIL : </b>'.$row->email.'
								<br/> <b>TELPON : </b>'.$row->tlp.'								
								<br/> <b>ALAMAT : </b>'.$row->alamat_supplier.' 
							</td>';
						
						if($this->session->userdata('ses_id_karyawan') <> $row->user_ins)
						{
							/*
							echo'<td>
								<button type="button" onclick="" class="btn btn-success btn-block btn-flat" data-dismiss="modal">CETAK</button>
							</td>';
							*/
							
							echo'
								<a class="confirm-btn btn btn-success btn-block btn-flat" href="'.base_url().'gl-admin-purchase-order-print-po/'.md5($row->id_h_pembelian).'" title = "Cetak PO '.$row->no_h_pembelian.'" alt = "Cetak PO '.$row->no_h_pembelian.'">CETAK</a>
							';
						}
						else
						{
							if($row->sts_pembelian == "PENDING")
							{
								echo'<td>
									<a class="confirm-btn btn btn-primary btn-block btn-flat" href="'.base_url().'gl-admin-purchase-order/'.($row->id_h_pembelian).'" title = "Lanjutkan transaksi Data '.$row->no_h_pembelian.'" alt = "Lanjutkan transaksi Data '.$row->no_h_pembelian.'">LANJUT</a>
									
									<button type="button" onclick="hapus_all('.$no.')" class="btn btn-danger btn-block  btn-flat" data-dismiss="modal">BATAL</button>
								</td>';
							}
							else
							{
								echo'<td>
									<!--
									<button type="button" onclick="" class="btn btn-success btn-block btn-flat" data-dismiss="modal">CETAK</button>
									-->
									
									<!--
									<a class="confirm-btn btn btn-success btn-block btn-flat" href="'.base_url().'gl-admin-purchase-order-print-po/'.md5($row->id_h_pembelian).'" title = "Cetak PO '.$row->no_h_pembelian.'" alt = "Cetak PO '.$row->no_h_pembelian.'">CETAK</a>
									-->
									
									<button type="button" onclick="cetak_faktur('.$no.')" class="btn btn-success btn-block  btn-flat" data-dismiss="modal">CETAK</button>
									
									<a class="confirm-btn btn btn-primary btn-block btn-flat" href="'.base_url().'gl-admin-purchase-order/'.($row->id_h_pembelian).'" title = "Ubah Data '.$row->no_h_pembelian.'" alt = "Ubah Data '.$row->no_h_pembelian.'">UBAH</a>
									
									<button type="button" onclick="hapus_all('.$no.')" class="btn btn-danger btn-block  btn-flat" data-dismiss="modal">HAPUS</button>
								</td>';
							}
								
						}
						
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
	
	function print_po()
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
					$id_h_pembelian = $this->uri->segment(2,0);
					//CEK VALIDASI H PEMBELIAN
					$cari_pembelian = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_h_pembelian) = '".$id_h_pembelian."' ";
					$cek_h_pembelian  = $this->M_gl_h_pembelian->get_h_pembelian_cari($cari_pembelian);
					
					if(!empty($cek_h_pembelian))
					{
						$cek_h_pembelian = $cek_h_pembelian->row(); //INFO H PEMBELIAN
						
						//INFO PEMBAYARAN
						$cari_d_pembelian_bayar = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_h_pembelian) = '".$id_h_pembelian."' ";
						$cek_d_pembelian_bayar = $this->M_gl_d_pembelian_bayar->get_d_pembelian_bayar_akumulasi_by_query($cari_d_pembelian_bayar);
						//INFO PEMBAYARAN
						
						//INFO D PEMBELIAN
						$cek_d_pembelian = $this->M_gl_d_pembelian->get_d_pembelian_by_query_for_cetak_po($cek_h_pembelian->id_supplier,$cari_d_pembelian_bayar);
						//INFO D PEMBELIAN
						
						$data = array('page_content'=>'admin/page/gl_admin_print_po.html','cek_h_pembelian' => $cek_h_pembelian,'cek_d_pembelian_bayar' => $cek_d_pembelian_bayar, 'cek_d_pembelian' => $cek_d_pembelian);
						$this->load->view('admin/page/gl_admin_print_po.html',$data);
					}
					else
					{
						header('Location: '.base_url().'gl-admin-purchase-order');
					}
					//CEK VALIDASI H PEMBELIAN
					
				}
				else
				{
					header('Location: '.base_url().'gl-admin-purchase-order');
				}
				
				
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	function closing_po()
	{
		if((!empty($_POST['id_h_pembelian'])) && ($_POST['id_h_pembelian']!= "")  )
		{
			$id_h_pembelian = $_POST['id_h_pembelian'];
			$kode_kantor = $_POST['kode_kantor'];
			$this->M_gl_h_pembelian->closing_po($kode_kantor,$id_h_pembelian);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan Closing pada PO '.$_POST['no_h_pembelian'].' | '.$_POST['id_h_pembelian'],
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
			
			echo"BERHASIL CLOSING ID H PEMBELIAN";
		}
		else
		{
			echo"TIDAK DITEMUKAN ID H PEMBELIAN";
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */
