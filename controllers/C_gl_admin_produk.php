<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_produk extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_gl_pst_produk','M_gl_produk','M_gl_supplier','M_gl_kat_costumer','M_gl_satuan'));
		
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
				if((!empty($_GET['cari_jenis_moving'])) && ($_GET['cari_jenis_moving']!= "")  )
				{
					$cari_jenis_moving = $_GET['cari_jenis_moving'];
				}
				else
				{
					$cari_jenis_moving = "";
				}
				
				if((!empty($_GET['cari_jenis_produk'])) && ($_GET['cari_jenis_produk']!= "")  )
				{
					$cari_jenis_produk = $_GET['cari_jenis_produk'];
				}
				else
				{
					$cari_jenis_produk = "";
				}
				
				
				if($this->session->userdata('ses_gnl_isPajakAktif')  == "Y") 
				{
					$isPajakaON = "AND A.isNPKP <> 'TIDAK'";
				}
				else
				{
					$isPajakaON = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND A.jenis_moving LIKE '%".$cari_jenis_moving."%'
							AND A.isProduk LIKE '%".$cari_jenis_produk."%'
							".$isPajakaON."
							AND (A.kode_produk LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_produk LIKE '%".str_replace("'","",$_GET['cari'])."%')
							";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							AND A.jenis_moving LIKE '%".$cari_jenis_moving."%'
							AND A.isProduk LIKE '%".$cari_jenis_produk."%'
							".$isPajakaON."
							";
				}
				
				$order_by = " ORDER BY A.tgl_ins DESC ";
				
				$jum_row = $this->M_gl_produk->count_produk_limit($cari)->JUMLAH;
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-produk-jasa?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-produk-jasa/');
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
				
				$list_produk = $this->M_gl_produk->list_produk_limit($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				$msgbox_title = " Produk dan Jasa";
				
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
				
				$list_satuan = $this->M_gl_satuan->list_satuan_limit(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'",30,0);
				$list_kat_costumer = $this->M_gl_kat_costumer->list_kat_costumer_limit(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'",30,0);
				
				$kode_produk = $this->M_gl_produk->get_kode_produk($this->session->userdata('ses_kode_kantor'));
				if(!empty($kode_produk))
				{
					$kode_produk = $kode_produk->row();
					$kode_produk = $kode_produk->kode_produk;
				}
				else
				{
					$kode_produk = "";
				}
				
				$data = array('page_content'=>'gl_admin_produk','halaman'=>$halaman,'list_produk'=>$list_produk,'msgbox_title' => $msgbox_title, 'sum_pesan' => $sum_pesan,'list_satuan' => $list_satuan, 'list_kat_costumer' => $list_kat_costumer,'kode_produk' => $kode_produk);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	/*
	function simpan()
	{
		echo $_POST['buf_stock'];
	}
	*/
	
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
				if( (!empty($_POST['stat_edit'])) && ($_POST['isSattle'] == 'on') )
				{
					$isSattle = 1;
				}
				else
				{
					$isSattle = 0;
				}
				
				if (!empty($_POST['stat_edit']))
				{	
					if($this->session->userdata('ses_kode_kantor') == 'PST')
					{
						$this->M_gl_pst_produk->edit
						(
							$_POST['stat_edit'],
							$_POST['id_supplier'],
							$_POST['kode_produk'],
							$_POST['nama_produk'],
							$_POST['nama_umum'],
							$_POST['produksi_oleh'],
							$_POST['pencipta'],
							str_replace(",","",$_POST['charge']) , //$_POST['charge'],
							$_POST['optr_charge'],
							'0', //$_POST['charge_beli'],
							'', //$_POST['optr_charge_beli'],
							$_POST['min_stock'],
							$_POST['max_stock'],
							'0', //$_POST['harga_minimum'],
							$_POST['spek_produk'],
							$_POST['ket_produk'],
							$_POST['isProduk'],
							$_POST['kat_costumer_fee'],
							$_POST['optr_kondisi_fee'],
							str_replace(",","",$_POST['besar_penjualan_fee']) , //$_POST['besar_penjualan_fee'],
							$_POST['satuan_jual_fee'],
							$_POST['optr_fee'],
							str_replace(",","",$_POST['besar_fee']) , //$_POST['besar_fee'],
							$isSattle,
							$_POST['buf_stock'],
							$_POST['late_time'],
							$_POST['jenis_moving'],
							$_POST['isNPKP'],
							$this->session->userdata('ses_id_karyawan')
						);
					}
					else
					{
						$this->M_gl_produk->edit
						(
							$_POST['stat_edit'],
							$_POST['id_supplier'],
							$_POST['kode_produk'],
							$_POST['nama_produk'],
							$_POST['nama_umum'],
							$_POST['produksi_oleh'],
							$_POST['pencipta'],
							str_replace(",","",$_POST['charge']) , //$_POST['charge'],
							$_POST['optr_charge'],
							'0', //$_POST['charge_beli'],
							'', //$_POST['optr_charge_beli'],
							$_POST['min_stock'],
							$_POST['max_stock'],
							'0', //$_POST['harga_minimum'],
							$_POST['spek_produk'],
							$_POST['ket_produk'],
							$_POST['isProduk'],
							$_POST['kat_costumer_fee'],
							$_POST['optr_kondisi_fee'],
							str_replace(",","",$_POST['besar_penjualan_fee']) , //$_POST['besar_penjualan_fee'],
							$_POST['satuan_jual_fee'],
							$_POST['optr_fee'],
							str_replace(",","",$_POST['besar_fee']) , //$_POST['besar_fee'],
							$isSattle,
							$_POST['buf_stock'],
							$_POST['late_time'],
							$_POST['jenis_moving'],
							$_POST['isNPKP'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					
					/* CATAT AKTIFITAS EDIT*/
					if($this->session->userdata('catat_log') == 'Y')
					{
						$this->M_gl_log->simpan_log
						(
							$this->session->userdata('ses_id_karyawan'),
							'UPDATE',
							'Melakukan perubahan data produk '.$_POST['kode_produk'].' | '.$_POST['nama_produk'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					if($this->session->userdata('ses_kode_kantor') == 'PST')
					{
						$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
						$id_produk = $this->M_gl_pst_produk->get_id_produk();
						
						if(!empty($list_kantor))
						{
							$list_result = $list_kantor->result();
							
							foreach($list_result as $row)
							{
								$this->M_gl_pst_produk->simpan
								(
									$id_produk->id_produk,
									$_POST['id_supplier'],
									$_POST['kode_produk'],
									$_POST['nama_produk'],
									$_POST['nama_umum'],
									$_POST['produksi_oleh'],
									$_POST['pencipta'],
									str_replace(",","",$_POST['charge']) , //$_POST['charge'],
									$_POST['optr_charge'],
									'0', //$_POST['charge_beli'],
									'', //$_POST['optr_charge_beli'],
									$_POST['min_stock'],
									$_POST['max_stock'],
									'0', //$_POST['harga_minimum'],
									$_POST['spek_produk'],
									$_POST['ket_produk'],
									'0', //$_POST['isNotActive'],
									'0', //$_POST['isNotRetur'],
									'0',
									'', //$_POST['dtstock'],
									$_POST['isProduk'],
									$_POST['kat_costumer_fee'],
									$_POST['optr_kondisi_fee'],
									str_replace(",","",$_POST['besar_penjualan_fee']) , //$_POST['besar_penjualan_fee'],
									$_POST['satuan_jual_fee'],
									$_POST['optr_fee'],
									str_replace(",","",$_POST['besar_fee']) , //$_POST['besar_fee'],
									$isSattle,
									$_POST['buf_stock'],
									$_POST['late_time'],
									$_POST['jenis_moving'],
									0, //HPP
									$_POST['isNPKP'],
									$this->session->userdata('ses_id_karyawan'),
									$row->kode_kantor //$this->session->userdata('ses_kode_kantor')
									
								);
							}
						}
					}
					else
					{
						$this->M_gl_produk->simpan
						(
							$_POST['id_supplier'],
							$_POST['kode_produk'],
							$_POST['nama_produk'],
							$_POST['nama_umum'],
							$_POST['produksi_oleh'],
							$_POST['pencipta'],
							str_replace(",","",$_POST['charge']) , //$_POST['charge'],
							$_POST['optr_charge'],
							'0', //$_POST['charge_beli'],
							'', //$_POST['optr_charge_beli'],
							$_POST['min_stock'],
							$_POST['max_stock'],
							'0', //$_POST['harga_minimum'],
							$_POST['spek_produk'],
							$_POST['ket_produk'],
							'0', //$_POST['isNotActive'],
							'0', //$_POST['isNotRetur'],
							'0',
							'', //$_POST['dtstock'],
							$_POST['isProduk'],
							$_POST['kat_costumer_fee'],
							$_POST['optr_kondisi_fee'],
							str_replace(",","",$_POST['besar_penjualan_fee']) , //$_POST['besar_penjualan_fee'],
							$_POST['satuan_jual_fee'],
							$_POST['optr_fee'],
							str_replace(",","",$_POST['besar_fee']) , //$_POST['besar_fee'],
							$isSattle,
							$_POST['buf_stock'],
							$_POST['late_time'],
							$_POST['jenis_moving'],
							0, //HPP
							$_POST['isNPKP'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
							
							
						);
					}
				}
				header('Location: '.base_url().'gl-admin-produk-jasa');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function hapus()
	{
		if($this->session->userdata('ses_kode_kantor') == 'PST')
		{
			$id_produk = ($this->uri->segment(2,0));
			$data_produk = $this->M_gl_pst_produk->get_produk('id_produk',$id_produk);
			if(!empty($data_produk))
			{
				$data_produk = $data_produk->row();
				$this->M_gl_pst_produk->hapus('id_produk',$id_produk);
				
				/* CATAT AKTIFITAS HAPUS*/
				if($this->session->userdata('catat_log') == 'Y')
				{
					$this->M_gl_log->simpan_log
					(
						$this->session->userdata('ses_id_karyawan'),
						'DELETE',
						'Melakukan penghapusan data produk dan jasa '.$data_produk->kode_produk.' | '.$data_produk->nama_produk,
						$this->M_gl_pengaturan->getUserIpAddr(),
						gethostname(),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				/* CATAT AKTIFITAS HAPUS*/
			}
		}
		else
		{
			$id_produk = ($this->uri->segment(2,0));
			$data_produk = $this->M_gl_produk->get_produk('id_produk',$id_produk);
			if(!empty($data_produk))
			{
				$data_produk = $data_produk->row();
				$this->M_gl_produk->hapus('id_produk',$id_produk);
				
				/* CATAT AKTIFITAS HAPUS*/
				if($this->session->userdata('catat_log') == 'Y')
				{
					$this->M_gl_log->simpan_log
					(
						$this->session->userdata('ses_id_karyawan'),
						'DELETE',
						'Melakukan penghapusan data produk '.$data_produk->kode_produk.' | '.$data_produk->nama_produk,
						$this->M_gl_pengaturan->getUserIpAddr(),
						gethostname(),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				/* CATAT AKTIFITAS HAPUS*/
			}
		}
		
		header('Location: '.base_url().'gl-admin-produk-jasa');
	}
	
	function cek_produk()
	{
		$hasil_cek = $this->M_gl_produk->get_produk('kode_produk',$_POST['kode_produk']);
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			echo $hasil_cek->kode_produk;
		}
		else
		{
			return false;
		}
		
	}
	
	function list_supplier()
	{
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (COALESCE(A.kode_supplier,'') LIKE '%".$_POST['cari']."%' OR COALESCE(A.nama_supplier,'') LIKE '%".$_POST['cari']."%')
							";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
		}
		
		
		$list_supplier = $this->M_gl_supplier->list_supplier_limit($cari,$_POST['limit'],$_POST['offset']);
		
		//($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_supplier))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="20%">DATA SUPPLIER</th>';
							echo '<th width="45%">KETERANGAN</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_supplier->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_'.$no.'">';
					
						echo'<td>'.$no.'</td>';
						echo'<td>
								<b>KODE : </b>'.$row->kode_supplier.' 
								<br/> <b>NAMA : </b>'.$row->nama_supplier.' 
								<br/> <b>PEMILIK : </b>'.$row->pemilik_supplier.'
								<br/> <b>JENIS USAHA : </b>'.$row->bidang.'
							</td>';
						
						echo'<td>
								<b>TLP : </b>'.$row->tlp.' 
								<br/> <b>EMAIL : </b>'.$row->email.' 
								<br/> <b>ALAMAT : </b>'.$row->alamat.'
							</td>';
						echo'<td>
								<button type="button" onclick="insert_supplier('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button>
							</td>';
						echo'<input type="hidden" id="id_supplier_3_'.$no.'" name="id_supplier_3_'.$no.'" value="'.$row->id_supplier.'" />';
						echo'<input type="hidden" id="kode_supplier_3_'.$no.'" name="kode_supplier_3_'.$no.'" value="'.$row->kode_supplier.'" />';
						echo'<input type="hidden" id="nama_supplier_3_'.$no.'" name="nama_supplier_3_'.$no.'" value="'.$row->nama_supplier.'" />';
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
	
	function cek_isNonPkp()
	{
		$data_produk = $this->M_gl_produk->get_produk('id_produk',$_POST['id_produk']);
		if(!empty($data_produk))
		{
			$data_produk = $data_produk->row();
			
			$query = "
					UPDATE tb_produk 
					SET 
						isNPKP = CASE WHEN isNPKP = 'TIDAK' THEN 'YA' ELSE 'TIDAK' END  
					WHERE id_produk = '".$data_produk->id_produk."' 
					AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
					";
					
			$this->M_gl_produk->exec_gen_query($query);
			
		}
		else
		{
			echo "PRODUK TIDAK DITEMUKAN";
		}
	}
	
	
	function cek_isNotActive()
	{
		$data_produk = $this->M_gl_produk->get_produk('id_produk',$_POST['id_produk']);
		if(!empty($data_produk))
		{
			$data_produk = $data_produk->row();
			
			if($this->session->userdata('ses_kode_kantor') == 'PST')
			{
				$this->M_gl_pst_produk->ubah_aktif($_POST['id_produk'],'isNotActive');
				echo "BERHASIL";
			}
			else
			{
				$this->M_gl_produk->ubah_aktif($_POST['id_produk'],$this->session->userdata('ses_kode_kantor'),'isNotActive');
				echo "BERHASIL";
			}
			
		}
		else
		{
			echo "PRODUK TIDAK DITEMUKAN";
		}
	}
	
	function cek_isReport()
	{
		$data_produk = $this->M_gl_produk->get_produk('id_produk',$_POST['id_produk']);
		if(!empty($data_produk))
		{
			$data_produk = $data_produk->row();
			//$this->M_gl_produk->ubah_aktif($_POST['id_produk'],$this->session->userdata('ses_kode_kantor'),'isReport');
			//echo "BERHASIL";
			
			if($this->session->userdata('ses_kode_kantor') == 'PST')
			{
				$this->M_gl_pst_produk->ubah_aktif($_POST['id_produk'],'isReport');
				echo "BERHASIL";
			}
			else
			{
				$this->M_gl_produk->ubah_aktif($_POST['id_produk'],$this->session->userdata('ses_kode_kantor'),'isReport');
				echo "BERHASIL";
			}
		}
		else
		{
			echo "PRODUK TIDAK DITEMUKAN";
		}
	}
	
	function list_kategori_produk()
	{	
		$list_kat_produk = $this->M_gl_produk->list_kategori_produk($_POST['id_produk'],$this->session->userdata('ses_kode_kantor'));
		
		if(!empty($list_kat_produk))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="75%">KATEGORI</th>';
							echo '<th width="20%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_kat_produk->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_'.$no.'">';
					
						echo'<td>'.$no.'</td>';
						echo'<td>'.$row->nama_kat_produk.'</td>';
						
						if($row->AKTIF == '1')
						{
							echo'<td>
								<div class="form-group">
									<label>
										<input type="checkbox" id="isAktif_'.$no.'" name="isAktif_'.$no.'" class="flat-red" onclick="cek_aktif_kategori('.$no.')" checked>
									</label>
								</div>
							</td>';
						}
						else
						{
							echo'<td>
								<div class="form-group">
									<label>
										<input type="checkbox"  id="isAktif_'.$no.'" name="isAktif_'.$no.'" class="flat-red" onclick="cek_aktif_kategori('.$no.')">
									</label>
								</div>
							</td>';
						}
						
						echo'<input type="hidden" id="id_kat_produk_3_'.$no.'" name="id_kat_produk_3_'.$no.'" value="'.$row->id_kat_produk.'" />';
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
	
	
	function cek_isNotActiveKategori()
	{
		$cari = " WHERE A.id_produk = '".$_POST['id_produk']."' AND A.id_kat_produk = '".$_POST['id_kat_produk']."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."';";
		
		
		$cek_kat_produk = $this->M_gl_produk->cek_kategori_produk($cari);
		
		
		if(!empty($cek_kat_produk))
		{
			$cek_kat_produk = $cek_kat_produk->row();
			
			if($this->session->userdata('ses_kode_kantor') == 'PST')
			{
				$this->M_gl_pst_produk->hapus_kat_to_prod($_POST['id_produk'],$_POST['id_kat_produk']);
			}
			else
			{
				$this->M_gl_produk->hapus_kat_to_prod($_POST['id_produk'],$_POST['id_kat_produk'],$this->session->userdata('ses_kode_kantor'));
			}
			
			
			$cari2 = " WHERE A.id_produk = '".$_POST['id_produk']."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."';";
			$cek_hari2 = $this->M_gl_produk->cek_kategori_produk($cari2);
			$hasil_hari = "";
			if(!empty($cek_hari2))
			{
				$list_result = $cek_hari2->result();
				foreach($list_result as $row)
				{
					$hasil_hari = $hasil_hari.','.$row->NAMA_KATEGORI;
				}
			}
			
			$hasil_hari = "Kategori Aktif : ".$hasil_hari;
			//$hasil_hari = $hasil_hari;
			
			//echo "BERHASIL";
		}
		else
		{
			if($this->session->userdata('ses_kode_kantor') == 'PST')
			{
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
				if(!empty($list_kantor))
				{
					$list_result = $list_kantor->result();
					
					foreach($list_result as $row)
					{
						$this->M_gl_pst_produk->simpan_kat_to_prod($_POST['id_produk'],$_POST['id_kat_produk'],$row->kode_kantor);
					}
				}
			}
			else
			{
				$this->M_gl_produk->simpan_kat_to_prod($_POST['id_produk'],$_POST['id_kat_produk'],$this->session->userdata('ses_kode_kantor'));
			}
			//$this->M_gl_produk->simpan_kat_to_prod($_POST['id_produk'],$_POST['id_kat_produk'],$this->session->userdata('ses_kode_kantor'));
			
			
			$cari2 = " WHERE A.id_produk = '".$_POST['id_produk']."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."';";
			$cek_hari2 = $this->M_gl_produk->cek_kategori_produk($cari2);
			$hasil_hari = "";
			if(!empty($cek_hari2))
			{
				$list_result = $cek_hari2->result();
				foreach($list_result as $row)
				{
					$hasil_hari = $hasil_hari.','.$row->NAMA_KATEGORI;
				}
			}
			
			//$hasil_hari = "Kategori Aktif : ".$hasil_hari;
			$hasil_hari = $hasil_hari;
			
		}
		echo $hasil_hari;
	}

	function list_kantor_produk_active()
	{	
		$list_kantor = $this->M_gl_produk->list_kantor_with_produk_active($_POST['id_produk'],$this->session->userdata('ses_kode_kantor'));
	
		if(!empty($list_kantor))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="75%">NAMA CABANG</th>';
							echo '<th width="20%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_kantor->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_'.$no.'">';
					
						echo'<td>'.$no.'</td>';
						echo'<td>'.$row->nama_kantor.'</td>';
						
						if($row->isNotActive == '0')
						{
							echo'<td>
								<div class="form-group">
									<label>
										<input type="checkbox" id="isAktif_kantor_'.$no.'" name="isAktif_kantor_'.$no.'" class="flat-red" onclick="cek_kantor_produk_aktive('.$no.')" checked>
									</label>
								</div>
							</td>';
						}
						else
						{
							echo'<td>
								<div class="form-group">
									<label>
										<input type="checkbox"  id="isAktif_kantor_'.$no.'" name="isAktif_kantor_'.$no.'" class="flat-red" onclick="cek_kantor_produk_aktive('.$no.')">
									</label>
								</div>
							</td>';
						}
						
						echo'<input type="hidden" id="kode_kantor_produk_'.$no.'" name="kode_kantor_produk_'.$no.'" value="'.$row->kode_kantor.'" />';
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

	function kantor_cek_aktif_produk()
	{
		$this->M_gl_produk->ubah_aktif($_POST['id_produk'],$_POST['kode_kantor'],'isNotActive');
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */