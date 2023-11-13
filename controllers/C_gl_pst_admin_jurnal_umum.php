<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_pst_admin_jurnal_umum extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_pst_uang_keluar','M_gl_bank','M_gl_kode_akun','M_gl_pengaturan','M_gl_supplier','M_gl_jurnal_umum','M_gl_h_penerimaan'));
		
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
					$cari = 
						"
							WHERE A.kode_kantor = '".$kode_kantor."' 
							AND DATE(A.tgl_dikeluarkan) BETWEEN '".$dari."' AND '".$sampai."'
							AND A.id_kat_uang_keluar2 = '' -- Hanya menampilkan uang keluar induk
							AND (
									A.no_uang_keluar LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.nama_uang_keluar LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR A.diberikan_kepada LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR A.untuk LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
						";
				}
				else
				{
					$cari = 
						"
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND DATE(A.tgl_dikeluarkan) BETWEEN '".$dari."' AND '".$sampai."'
							AND A.id_kat_uang_keluar2 = '' -- Hanya menampilkan uang keluar induk
						";
				}
				
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("")->result();
				$msgbox_title = "Jurnal Umum Akuntansi";
				
				
				//JURNAL UMUM DIBUAT SATU SAJA DENGAN MENGGUNAKAN CASE IF, JIKA PUSAT AMBIL DARI PUSAT JIKA CABANG AMBIL DAR CABANG
				if( $this->session->userdata('ses_kode_kantor') == "PUSAT" )
				{
					$data = array('page_content'=>'c_gl_akuntansi/gl_admin_jurnal_umum',
							  
							  
							  'msgbox_title' => $msgbox_title,
							  //'sum_pesan' => $sum_pesan,
							  //'list_bank'=>$list_bank,
							  //'get_no_uang_keluar'=>$get_no_uang_keluar,
							  //'sum_uang_keluar'=>$sum_uang_keluar,
							  //'list_kode_akun' => $list_kode_akun
							  'list_kantor' => $list_kantor,
								);
								
					$this->load->view('pusat/container',$data);
				}
				else
				{
					$data = array('page_content'=>'c_gl_akuntansi/gl_admin_jurnal_umum',
							  
							  
							  'msgbox_title' => $msgbox_title,
							  //'sum_pesan' => $sum_pesan,
							  //'list_bank'=>$list_bank,
							  //'get_no_uang_keluar'=>$get_no_uang_keluar,
							  //'sum_uang_keluar'=>$sum_uang_keluar,
							  //'list_kode_akun' => $list_kode_akun
							  'list_kantor' => $list_kantor,
								);
					$this->load->view('admin/container',$data);
				}
				//JURNAL UMUM DIBUAT SATU SAJA DENGAN MENGGUNAKAN CASE IF, JIKA PUSAT AMBIL DARI PUSAT JIKA CABANG AMBIL DAR CABANG
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function list_kode_akun()
	{
		$cari_coa = "WHERE kode_kantor = '".$_POST['kode_kantor']."'";
		$order_by_coa = " ORDER BY nama_kode_akun ASC ";
		$list_kode_akun = $this->M_gl_kode_akun->list_kode_akun_limit_order_by($cari_coa,$order_by_coa,1000,0);

		echo '<option value=""></option>';
									
		if(!empty($list_kode_akun))
		{
			$list_result = $list_kode_akun->result();
			foreach($list_result as $row)
			{
				echo'<option value="'.$row->id_kode_akun.'|'.$row->id_supplier.'">'.$row->nama_kode_akun.'|'.$row->kode_akun.'</option>';
			}
		}
	}
	
	function get_no_bukti_jurnal()
	{
		$no_bukti = $this->M_gl_jurnal_umum->get_no_jurnal_umum($_POST['kode_kantor']);
		if(!empty($no_bukti))
		{
			$no_bukti = $no_bukti->row();
			$no_bukti = $no_bukti->no_bukti;
			echo $no_bukti;
		}
		else
		{
			echo"";
		}
	}
	
	function simpan_jurnal_umum()
	{
		$cari = " WHERE 
					kode_kantor = '".$_POST['kode_kantor']."'
					AND no_bukti = '".$_POST['no_bukti']."'
					AND kode_akun = '".$_POST['kode_akun']."'
				";
		$info_jurnal_umum = $this->M_gl_jurnal_umum->get_cari_tb_h_jurnal_umum($cari,"","");
		if(!empty($info_jurnal_umum))
		{
			//EDIT
			$info_jurnal_umum = $info_jurnal_umum->row();
			
			
			//PASTIKAN YANG DIMASUKAN KE DEBIT DAN KREDIT ADALAH ANGKA, KARENA ADA KEMUNGKINAN DATA YANG MASUK ADALAH STR NAMA AKUN
				if(is_numeric($_POST['debit']) == 1)
				{
					$debit = $_POST['debit'];
					/*
					if($debit == 0)
					{
						$debit = $info_jurnal_umum->debit;
					}
					*/
				}
				else
				{
					$debit = $info_jurnal_umum->debit;
				}
				
				if(is_numeric($_POST['kredit']) == 1)
				{
					$kredit = $_POST['kredit'];
					/*
					if($kredit == 0)
					{
						$kredit = $info_jurnal_umum->kredit;
					}
					*/
				}
				else
				{
					$kredit = $info_jurnal_umum->kredit;
				}
			//PASTIKAN YANG DIMASUKAN KE DEBIT DAN KREDIT ADALAH ANGKA, KARENA ADA KEMUNGKINAN DATA YANG MASUK ADALAH STR NAMA AKUN
			
			$this->M_gl_jurnal_umum->edit(
					$info_jurnal_umum->id_jurnal_umum,
					$_POST['id_kode_akun'],
					$_POST['id_supplier'],
					$_POST['id_h_penerimaan'],
					$_POST['no_bukti'],
					$_POST['kode_akun'],
					$_POST['nama_akun'],
					$_POST['tgl_transaksi'],
					$_POST['ket'],
					$debit, //$_POST['debit'],
					$kredit, //$_POST['kredit'],
					$info_jurnal_umum->isDebit,
					$info_jurnal_umum->idx_induk,
					$info_jurnal_umum->idx_chld,
					$this->session->userdata('ses_id_karyawan'),
					$_POST['kode_kantor']
				);
			
			//echo $debit;
			echo"Berhasil Ubah";
		}
		else
		{
			//SIMPAN
			//$get_id
			
			$this->M_gl_jurnal_umum->simpan(
					$_POST['id_kode_akun'],
					$_POST['id_supplier'],
					$_POST['id_h_penerimaan'],
					$_POST['no_bukti'],
					$_POST['kode_akun'],
					$_POST['nama_akun'],
					$_POST['tgl_transaksi'],
					$_POST['ket'],
					$_POST['debit'],
					$_POST['kredit'],
					$_POST['isDebit'],
					$_POST['idx_induk'],
					$_POST['idx_chld'],
					$this->session->userdata('ses_id_karyawan'),
					$_POST['kode_kantor']
				);
			
			echo"Berhasil Simpan";
		}
	}
	
	function hapus()
	{
		$cari = "WHERE kode_kantor = '".$_POST['kode_kantor']."' AND no_bukti = '".$_POST['no_bukti']."' AND id_kode_akun = '".$_POST['id_kode_akun']."'";
		$this->M_gl_jurnal_umum->hapus($cari);
		
		echo"Berhasil Hapus";
	}
	
	function tampilkan_jurnal_umum()
	{
		$cari = "WHERE kode_kantor = '".$_POST['kode_kantor']."' AND no_bukti = '".$_POST['no_bukti']."'";
		$order_by = "ORDER BY idx_mix ASC, idx_induk ASC, idx_chld ASC";
		$list_jurnal = $this->M_gl_jurnal_umum->get_cari_tb_h_jurnal_umum($cari,$order_by,"");
		
		if(!empty($list_jurnal))
		{
			
				$list_result = $list_jurnal->result();
				$no = 0;
				
				foreach($list_result as $row)
				{
					//if($row->isDebit == 'debit')
					if($row->debit > 0)
					{
						if($row->idx_induk == $row->idx_chld)
						{
							echo '<tr id="row-'.$row->idx_chld.'">
									<td>
										<select name="kode_akun-'.$row->idx_chld.'" id="kode_akun-'.$row->idx_chld.'" class="form-control select2" title="Kode Akun/COA" style="width:100%;" width="100%" onclick="list_akun_row(this)" onchange="insert_nama_akun(this)">
											<option value="'.$row->id_kode_akun.'">'.$row->nama_akun.'|'.$row->kode_akun.'</option></select>
									</td>
									<td>
										<input type="text" onchange="simpan_debit(this)" class="form-control" id="nama_akun-'.$row->idx_chld.'" value="'.$row->ket.'" style="width:100%;"/></td><td><input type="text" id="debit-'.$row->idx_chld.'" class="debit form-control required" style="text-align:right;" maxlength="15" size="7" onkeypress="return isNumberKey(event)" onchange="simpan_debit(this)" onfocusout="getRupiah(this.id)" value="'.number_format($row->debit,0,'.',',').'" />
									</td>
									<td>
									</td>
									<td>
										<a style="float:right;padding:2%;" class="" href="javascript:void(0)" id="insert-'.$row->idx_chld.'" title = "Insert Jurnal" alt = "Insert Jurnal" onclick="insert_kredit_to_table(this)"><i class="fa fa-plus"></i></a>
									</td>
							
							<td>
								<a style="float:right;padding:2%;" class="" href="javascript:void(0)" id="hapus-'.$row->idx_chld.'" title = "Hapus Jurnal" alt = "Hapus Jurnal" onclick="hapus_row(this)"><i class="fa fa-minus"></i></a> 
							</td>
							<td>
							';
							
							if($row->id_supplier == "")
							{
								echo'
									<a style="float:right;padding:2%;visibility:hidden;" class="" href="javascript:void(0)" id="inv-'.$row->idx_chld.'" title = "Bayar Invoice" alt = "Bayar Invoice" onclick="view_invoice(this)" data-toggle="modal" data-target="#myModal_hutang"><i class="fa fa-file-text"></i></a>
								';
							}
							else
							{
								echo'
									<a style="float:right;padding:2%;" class="" href="javascript:void(0)" id="inv-'.$row->idx_chld.'" title = "Bayar Invoice" alt = "Bayar Invoice" onclick="view_invoice(this)" data-toggle="modal" data-target="#myModal_hutang"><i class="fa fa-file-text"></i></a>
								';
							}
							
							echo'
								<input type="hidden" id="id_uang_keluar-'.$row->idx_chld.'" style="text-align:right;" maxlength="25" size="7" class="required select2" value="'.$row->id_jurnal_umum.'"/> <input type="hidden" id="row_induk-'.$row->idx_chld.'" value="'.$row->idx_induk.'" /> <input type="hidden" id="row_chld-'.$row->idx_chld.'" value="'.$row->idx_chld.'" /> 
							
								<input type="hidden" id="id_supplier-'.$row->idx_chld.'" value="'.$row->id_supplier.'" />
								<input type="hidden" id="id_h_penerimaan-'.$row->idx_chld.'" value="'.$row->id_h_penerimaan.'" />
							</td>
							
							<td>
								<img src="'.base_url().'assets/global/true.png">
							</td>
							
							</tr>';
						}
						else
						{
							echo '
								<tr id="row-'.$row->idx_chld.'">
									<td><select name="kode_akun-'.$row->idx_chld.'" id="kode_akun-'.$row->idx_chld.'" class="form-control select2" title="Kode Akun/COA" style="width:100%;" width="100%" onclick="list_akun_row(this)" onchange="insert_nama_akun(this)"><option value="'.$row->id_kode_akun.'">'.$row->nama_akun.'|'.$row->kode_akun.'</option></select>
								</td>
								<td>
									<input type="text" onchange="simpan_debit(this)" class="form-control" id="nama_akun-'.$row->idx_chld.'" value="'.$row->ket.'" style="width:100%;"/>
								</td>
								<td>
									<input type="text" id="debit-'.$row->idx_chld.'" class="debit form-control required" style="text-align:right;" maxlength="15" size="7" onkeypress="return isNumberKey(event)" onchange="simpan_debit(this)" onfocusout="getRupiah(this.id)" value="'.number_format($row->debit,0,'.',',').'" />
								</td>
								<td></td>
								<td></td>
							<td>
								<a style="float:right;padding:2%;" class="" href="javascript:void(0)" id="hapus-'.$row->idx_chld.'" title = "Hapus Jurnal" alt = "Hapus Jurnal" onclick="hapus_row(this)"><i class="fa fa-minus"></i></a> 
							</td>
							<td>
							';
							
							if($row->id_supplier == "")
							{
								echo'
									<a style="float:right;padding:2%;visibility:hidden;" class="" href="javascript:void(0)" id="inv-'.$row->idx_chld.'" title = "Bayar Invoice" alt = "Bayar Invoice" onclick="view_invoice(this)" data-toggle="modal" data-target="#myModal_hutang"><i class="fa fa-file-text"></i></a>
								';
							}
							else
							{
								echo'
									<a style="float:right;padding:2%;" class="" href="javascript:void(0)" id="inv-'.$row->idx_chld.'" title = "Bayar Invoice" alt = "Bayar Invoice" onclick="view_invoice(this)" data-toggle="modal" data-target="#myModal_hutang"><i class="fa fa-file-text"></i></a>
								';
							}
							
							echo'
							
							<input type="hidden" id="id_uang_keluar-'.$row->idx_chld.'" style="text-align:right;" maxlength="25" size="7" class="required select2" value="'.$row->id_jurnal_umum.'"/> <input type="hidden" id="row_induk-'.$row->idx_chld.'" value="'.$row->idx_induk.'" /> <input type="hidden" id="row_chld-'.$row->idx_chld.'" value="'.$row->idx_chld.'" /> 
							
							<input type="hidden" id="id_supplier-'.$row->idx_chld.'" value="'.$row->id_supplier.'" />
							<input type="hidden" id="id_h_penerimaan-'.$row->idx_chld.'" value="'.$row->id_h_penerimaan.'" />
							
							</td>
							<td>
								<img src="'.base_url().'assets/global/true.png">
							</td>
							
							</tr>';
						}
						
					}
					else
					{
						if($row->idx_induk == $row->idx_chld)
						{
							echo '
								<tr id="row-'.$row->idx_chld.'">
									<td>
										<select name="kode_akun-'.$row->idx_chld.'" id="kode_akun-'.$row->idx_chld.'" class="form-control select2" title="Kode Akun/COA" style="width:100%;" width="100%" onclick="list_akun_row(this)" onchange="insert_nama_akun(this)"><option value="'.$row->id_kode_akun.'">'.$row->nama_akun.'|'.$row->kode_akun.'</option></select>
									</td>
									<td>
										<input type="text" onchange="simpan_debit(this)" class="form-control" id="nama_akun-'.$row->idx_chld.'" value="'.$row->ket.'" style="width:100%;" />
									</td>
									<td></td>
									<td>
										<input type="text" id="kredit-'.$row->idx_chld.'" class="kredit form-control required" style="text-align:right;" maxlength="15" size="7" onkeypress="return isNumberKey(event)" onchange="simpan_debit(this)" onfocusout="getRupiah(this.id)" value="'.number_format($row->kredit,0,'.',',').'" />
									</td>
									<td>
										<a style="float:right;padding:2%;" class="" href="javascript:void(0)" id="insert-'.$row->idx_chld.'" title = "Insert Jurnal" alt = "Insert Jurnal" onclick="insert_kredit_to_table(this)"><i class="fa fa-plus"></i></a>
									</td>
									<td>
										<a style="float:right;padding:2%;" class="" href="javascript:void(0)" id="hapus-'.$row->idx_chld.'" title = "Hapus Jurnal" alt = "Hapus Jurnal" onclick="hapus_row(this)"><i class="fa fa-minus"></i></a> 
										
										<input type="hidden" id="id_uang_keluar-'.$row->idx_chld.'" style="text-align:right;" maxlength="25" size="7" class="required select2" value="'.$row->id_jurnal_umum.'"/> 
										
										<input type="hidden" id="row_induk-'.$row->idx_chld.'" value="'.$row->idx_induk.'" /> <input type="hidden" id="row_chld-'.$row->idx_chld.'" value="'.$row->idx_chld.'" /> 
							
										<input type="hidden" id="id_supplier-'.$row->idx_chld.'" value="'.$row->id_supplier.'" />
										<input type="hidden" id="id_h_penerimaan-'.$row->idx_chld.'" value="'.$row->id_h_penerimaan.'" />
									</td>
									<td></td>
									<td>
										<img src="'.base_url().'assets/global/true.png">
									</td>
								</tr>';
						}
						else
						{
							echo '
								<tr id="row-'.$row->idx_chld.'">
									<td>
										<select name="kode_akun-'.$row->idx_chld.'" id="kode_akun-'.$row->idx_chld.'" class="form-control select2" title="Kode Akun/COA" style="width:100%;" width="100%" onclick="list_akun_row(this)" onchange="insert_nama_akun(this)">
											<option value="'.$row->id_kode_akun.'">'.$row->nama_akun.'|'.$row->kode_akun.'</option>
										</select>
									</td>
									<td>
										<input type="text" onchange="simpan_debit(this)" class="form-control" id="nama_akun-'.$row->idx_chld.'" value="'.$row->ket.'" style="width:100%;"/>
									</td>
									<td></td>
									<td>
										<input type="text" id="kredit-'.$row->idx_chld.'" class="kredit form-control required" style="text-align:right;" maxlength="15" size="7" onkeypress="return isNumberKey(event)" onchange="simpan_debit(this)" onfocusout="getRupiah(this.id)" value="'.number_format($row->kredit,0,'.',',').'" />
									</td>
									<td>
							
									<!--
									<a style="float:right;padding:2%;" class="" href="javascript:void(0)" id="insert-'.$row->idx_chld.'" title = "Insert Jurnal" alt = "Insert Jurnal" onclick="insert_kredit_to_table(this)"><i class="fa fa-plus"></i></a>
									-->
									</td>
									<td>
											<a style="float:right;padding:2%;" class="" href="javascript:void(0)" id="hapus-'.$row->idx_chld.'" title = "Hapus Jurnal" alt = "Hapus Jurnal" onclick="hapus_row(this)"><i class="fa fa-minus"></i></a> 
											
											<input type="hidden" id="id_uang_keluar-'.$row->idx_chld.'" style="text-align:right;" maxlength="25" size="7" class="required select2" value="'.$row->id_jurnal_umum.'"/> 
											
											<input type="hidden" id="row_induk-'.$row->idx_chld.'" value="'.$row->idx_induk.'" /> <input type="hidden" id="row_chld-'.$row->idx_chld.'" value="'.$row->idx_chld.'" /> 
									
										<input type="hidden" id="id_supplier-'.$row->idx_chld.'" value="'.$row->id_supplier.'" />
										<input type="hidden" id="id_h_penerimaan-'.$row->idx_chld.'" value="'.$row->id_h_penerimaan.'" />
									</td>
									
									<td></td>
									<td>
										<img src="'.base_url().'assets/global/true.png">
									</td>
								</tr>';
						}
						
					}
						
					$no++;
				}
		}
		else
		{
		}
	}
	
	function print_jurnal_umum()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			/*
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
			*/
				$cari = "WHERE MD5(kode_kantor) = '".$_GET['token']."' AND MD5(no_bukti) = '".$_GET['code']."'";
				$order_by = "ORDER BY idx_mix DESC, idx_induk DESC, idx_chld DESC";
				$list_jurnal = $this->M_gl_jurnal_umum->get_cari_tb_h_jurnal_umum($cari,$order_by,"");
				
				if(!empty($list_jurnal))
				{
					
					$get_jurnal = $this->M_gl_jurnal_umum->get_cari_tb_h_jurnal_umum_group_by($cari);
					$get_jurnal = $get_jurnal->row();
					$msgbox_title = " Jurnal Umum";
					
					$data = array('page_content'=>'gl_admin_print_jurnal_umum','list_jurnal'=>$list_jurnal,'get_jurnal' => $get_jurnal);
					
					//$this->load->view('admin/container',$data);
					$this->load->view('pusat/page/c_gl_akuntansi/gl_admin_print_jurnal_umum.html',$data);
				}
				else
				{
					//header('Location: '.base_url().'gl-admin');
					echo"Tidak ada data yang ditampilkan";
				}
			/*	
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
			*/
		}
	}
	
	function tampilkan_no_saja_jurnal_umum()
	{
		$cari = "WHERE kode_kantor = '".$_POST['kode_kantor']."' AND no_bukti LIKE '%".$_POST['no_bukti']."%'";
		$order_by = "ORDER BY tgl_ins DESC";
		$limit = "LIMIT 0,30";
		$list_jurnal = $this->M_gl_jurnal_umum->get_cari_tb_h_jurnal_umum_group_by_no($cari,$order_by,$limit);
		
		if(!empty($list_jurnal))
		{
			
				$list_result = $list_jurnal->result();
				$no = 0;
				
				echo'<table width="100%" id="example2" class="table table-hover">';
				echo'<thead>';
					echo'<tr style="background-color:black;">';
						echo'<td style="font-weight:bold;color:white;">NO BUKTI</td>';
						echo'<td style="font-weight:bold;color:white;">AKSI</td>';
					echo'</tr>';
				echo'</thead>';
				foreach($list_result as $row)
				{
					echo'<tr>';	
						echo'<td>';
							echo $row->no_bukti;
						echo'</td>';
						echo'<td>';
							echo'<button type="button" id="'.$row->no_bukti.'" onclick="insert_no(this)" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button>';
						echo'</td>';
					echo'</tr>';	
					$no++;
				}
				echo'</table>';
		}
		else
		{
		}
	}
	
	function simpan_jurnal_umum_debit_ke_uang_keluar()
	{	
		$cari = "WHERE 
				kode_kantor = '".$_POST['kode_kantor']."' 
				AND no_uang_keluar = '".$_POST['no_uang_keluar']."' 
				AND id_kat_uang_keluar = '".$_POST['kat_uang_keluar']."' 
			";
		$data_uang_keluar = $this->M_gl_pst_uang_keluar->get_uang_keluar_cari($cari);
		if(!empty($data_uang_keluar))
		{
			$data_uang_keluar = $data_uang_keluar->row();
		}
		else
		{
			if($_POST['isDebit'] == 'debit')
			{
				$this->M_gl_pst_uang_keluar->simpan_utama
				(
					$_POST['kat_uang_keluar'], //id_kat_uang_keluar
					'', //$_POST['id_kat_uang_keluar2'],
					'',//$_POST['id_costumer'],
					'',//$_POST['id_supplier'],
					'',//$_POST['id_karyawan'],
					'',//$_POST['id_proyek'],
					'', //$_POST['id_bank'],
					'',//$_POST['id_d_assets'],
					'',//$_POST['id_retur_penjualan'],
					'',//$_POST['id_retur_pembelian'],
					'',//$_POST['id_hadiah'],
					$_POST['no_uang_keluar'],
					$_POST['nama_uang_keluar'],
					'', //$_POST['diberikan_kepada'],
					'', //$_POST['diberikan_oleh'],
					'', //$_POST['untuk'],
					str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
					'',//$_POST['jenis'],
					$_POST['nama_uang_keluar'], //$_POST['ket_uang_keluar'],
					'0',//$_POST['isTabungan'],
					'0',//$_POST['isPinjamanCos'],
					$_POST['tgl_dikeluarkan'],
					$_POST['tgl_dikeluarkan'], //$_POST['tgl_diterima'],
					$this->session->userdata('ses_id_karyawan'),
					$_POST['kode_kantor']
				
				);
			}
			else
			{
				$this->M_gl_pst_uang_keluar->simpan
				(
					'', //$_POST['kat_uang_keluar'], //id_kat_uang_keluar
					$_POST['kat_uang_keluar'], //$_POST['id_kat_uang_keluar2'],
					'', //$_POST['id_induk_uang_keluar'],
					'',//$_POST['id_costumer'],
					'',//$_POST['id_supplier'],
					'',//$_POST['id_karyawan'],
					'',//$_POST['id_proyek'],
					'', //$_POST['id_bank'],
					'',//$_POST['id_d_assets'],
					'',//$_POST['id_retur_penjualan'],
					'',//$_POST['id_retur_pembelian'],
					'',//$_POST['id_hadiah'],
					$_POST['no_uang_keluar'],
					$_POST['nama_uang_keluar'],
					'', //$_POST['diberikan_kepada'],
					'', //$_POST['diberikan_oleh'],
					'', //$_POST['untuk'],
					str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
					'',//$_POST['jenis'],
					'', //$_POST['ket_uang_keluar'],
					'0',//$_POST['isTabungan'],
					'0',//$_POST['isPinjamanCos'],
					$_POST['tgl_dikeluarkan'],
					$_POST['tgl_dikeluarkan'], //$_POST['tgl_diterima'],
					$this->session->userdata('ses_id_karyawan'),
					$_POST['kode_kantor']
				
				);
			}
		}
	}
	
	
	public function list_penerimaan_for_hutang()
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
				/*
				$id_h_pembelian = $this->uri->segment(2,0);
				$data_h_pembelian = $this->M_gl_h_pembelian->get_h_pembelian('id_h_pembelian',$id_h_pembelian);
				if(!empty($data_h_pembelian))
				{
				*/
					//$data_h_pembelian = $data_h_pembelian->row();
					
					if((!empty($_POST['id_supplier'])) && ($_POST['id_supplier']!= "")  )
					{
						$id_supplier = $_POST['id_supplier'];
					}
					else
					{
						$id_supplier = '';
					}
					
					/*
					if((!empty($_POST['status_hutang'])) && ($_POST['status_hutang']!= "")  )
					{
						if($_POST['status_hutang'] == 'SISA')
						{
						*/
							$status_hutang = " AND (COALESCE(C.NOMINAL,0) - COALESCE(F.BAYAR,0)) > 0 ";
						/*
						}
						else
						{
							$status_hutang = " AND (COALESCE(C.NOMINAL,0) - COALESCE(F.BAYAR,0)) <= 0 ";
						}
					}
					else
					{
						$status_hutang = '';
					}
					*/
					
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
						//$cari = " WHERE A.id_h_pembelian = '".$id_h_pembelian."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.no_surat_jalan LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_pengirim LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.diterima_oleh LIKE '%".str_replace("'","",$_GET['cari'])."%' )";
						
						if($status_hutang == '')
						{
							$cari = " 
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							AND CASE WHEN '".$id_supplier."' <> '' THEN
									COALESCE(D.id_supplier,0) = '".$id_supplier."'
								ELSE
									COALESCE(D.id_supplier,0) LIKE '%".$id_supplier."%'
								END
							-- AND COALESCE(DATE(A.tgl_terima),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
							AND (
									A.no_surat_jalan LIKE '%".str_replace("'","",$_POST['cari'])."%' 
									OR A.nama_pengirim LIKE '%".str_replace("'","",$_POST['cari'])."%' 
									OR A.diterima_oleh LIKE '%".str_replace("'","",$_POST['cari'])."%' 
									OR A.diterima_oleh LIKE '%".str_replace("'","",$_POST['cari'])."%' 
									OR COALESCE(E.id_supplier,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
									OR COALESCE(E.nama_supplier,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
									OR COALESCE(D.no_h_pembelian,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
								)";
						}
						else
						{
							$cari = " 
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							AND CASE WHEN '".$id_supplier."' <> '' THEN
									COALESCE(D.id_supplier,0) = '".$id_supplier."'
								ELSE
									COALESCE(D.id_supplier,0) LIKE '%".$id_supplier."%'
								END
							".$status_hutang."
							AND (
									A.no_surat_jalan LIKE '%".str_replace("'","",$_POST['cari'])."%' 
									OR A.nama_pengirim LIKE '%".str_replace("'","",$_POST['cari'])."%' 
									OR A.diterima_oleh LIKE '%".str_replace("'","",$_POST['cari'])."%' 
									OR A.diterima_oleh LIKE '%".str_replace("'","",$_POST['cari'])."%' 
									OR COALESCE(E.id_supplier,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
									OR COALESCE(E.nama_supplier,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
									OR COALESCE(D.no_h_pembelian,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
								)";
						}
								
					}
					else
					{
						//$cari = " WHERE A.id_h_pembelian = '".$id_h_pembelian."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
						if($status_hutang == '')
						{
							$cari = " 
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND CASE WHEN '".$id_supplier."' <> '' THEN
									COALESCE(D.id_supplier,0) = '".$id_supplier."'
								ELSE
									COALESCE(D.id_supplier,0) LIKE '%".$id_supplier."%'
								END
							-- AND COALESCE(DATE(A.tgl_terima),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."' ";
						}
						else
						{
							$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND CASE WHEN '".$id_supplier."' <> '' THEN
									COALESCE(D.id_supplier,0) = '".$id_supplier."'
								ELSE
									COALESCE(D.id_supplier,0) LIKE '%".$id_supplier."%'
								END
							".$status_hutang." ";
						}
					}
					
					
					
					$list_h_penerimaan = $this->M_gl_h_penerimaan->list_h_penerimaan_limit_for_hutang_by_inv_pay_by_ju($cari,30,0);
					
					
					if(!empty($list_h_penerimaan))
					{
						//echo gethostname();
						//echo $this->M_gl_pengaturan->getUserIpAddr();
						//$sts_query = strpos(base_url(),"localhost");
						//echo $sts_query;
						//$nama = "Mulyana Yusuf";
						//echo str_replace("f","849",$nama);
						echo'<table width="100%" id="example2" class="table table-hover">';
							echo '<thead>
<tr>';
										echo '<th width="5%">NO</th>';
										echo '<th width="50%">SURAT JALAN</th>';
										echo '<th width="15%">NOMINAL</th>';
										echo '<th width="10%">BAYAR</th>';
										echo '<th width="10%">SISA</th>';
										echo '<th width="10%">PILIH</th>';
							echo '</tr>
</thead>';
							$list_result = $list_h_penerimaan->result();
							$no =1;
							
							$subtotalNominal = 0;
							$subTotalBayar = 0;
							$subTotalSisa = 0;
							
							echo '<tbody>';
							foreach($list_result as $row)
							{
								echo'<tr>';
									echo'<td>'.$no.'</td>';
									echo'
									<td>
										<b>PO : </b><i style="color:red;">'.$row->NO_PO.'</i>
										<br/> <b>NO SURAT : </b>'.$row->no_surat_jalan.'
										<br/> <b>NO BUKTI BAYAR : </b>'.$row->NO_BUKTI.'
									</td>';
									
									echo'<td style="text-align:right;">
														JUM : <font style="color:red;">'.number_format($row->NOMINAL,1,'.',',').'</font>
														<br/> BIAYA : <font style="color:red;">'.number_format($row->biaya_kirim,0,'.',',').'</font>
													</td>';
													
									echo'<td style="text-align:right;">'.number_format($row->BAYAR,1,'.',',').'</td>';
									echo'<td style="text-align:right;">'.number_format($row->SISA,1,'.',',').'</td>';
									
									echo'<td>
										<a style="background-color:green;color:black;" id="PILIH-'.$row->id_h_penerimaan.'-'.str_replace("-","",$row->no_surat_jalan).'-'.$row->SISA.'" href="javascript:void(0)" data-dismiss="modal" class="confirm-btn btn btn-success btn-block btn-flat" title = "Terima Produk Data '.$row->no_surat_jalan.'" alt = "Terima Produk Data '.$row->no_surat_jalan.'" onclick="simpan_debit(this)">PILIH</a>
									</td>';
									
									$subtotalNominal = $subtotalNominal + $row->NOMINAL;
									$subTotalBayar = $subTotalBayar + $row->BAYAR;
									$subTotalSisa = $subTotalSisa + $row->SISA;
									
//PEMBAYAAN JADI DARI UANG KELUAR, BUKAN DARI tb_d_pembelian lagi											
									
								echo'</tr>';
								$no++;
								
								
								echo'<input type="hidden" id="no_'.$no.'" name="no_'.$no.'" value="'.$no.'" />';
								echo'<input type="hidden" id="id_h_penerimaan_'.$no.'" name="id_h_penerimaan_'.$no.'" value="'.$row->id_h_penerimaan.'" />';
								echo'<input type="hidden" id="id_h_pembelian_'.$no.'" name="id_h_pembelian_'.$no.'" value="'.$row->id_h_pembelian.'" />';
								
								echo'<input type="hidden" id="id_gedung_'.$no.'" name="id_gedung_'.$no.'" value="'.$row->id_gedung.'" />';
								
								
								echo'<input type="hidden" id="no_surat_jalan_'.$no.'" name="no_surat_jalan_'.$no.'" value="'.$row->no_surat_jalan.'" />';
								echo'<input type="hidden" id="nama_pengirim_'.$no.'" name="nama_pengirim_'.$no.'" value="'.$row->nama_pengirim.'" />';
								echo'<input type="hidden" id="cara_pengiriman_'.$no.'" name="cara_pengiriman_'.$no.'" value="'.$row->cara_pengiriman.'" />';
								echo'<input type="hidden" id="diterima_oleh_'.$no.'" name="diterima_oleh_'.$no.'" value="'.$row->diterima_oleh.'" />';
								echo'<input type="hidden" id="biaya_kirim_'.$no.'" name="biaya_kirim_'.$no.'" value="'.$row->biaya_kirim.'" />';
								echo'<input type="hidden" id="tgl_kirim_'.$no.'" name="tgl_kirim_'.$no.'" value="'.$row->tgl_kirim.'" />';
								echo'<input type="hidden" id="tgl_terima_'.$no.'" name="tgl_terima_'.$no.'" value="'.$row->tgl_terima.'" />';
								echo'<input type="hidden" id="ket_h_penerimaan_'.$no.'" name="ket_h_penerimaan_'.$no.'" value="'.$row->ket_h_penerimaan.'" />';
								echo'<input type="hidden" id="tgl_ins_'.$no.'" name="tgl_ins_'.$no.'" value="'.$row->tgl_ins.'" />';
								echo'<input type="hidden" id="tgl_updt_'.$no.'" name="tgl_updt_'.$no.'" value="'.$row->tgl_updt.'" />';
								echo'<input type="hidden" id="user_updt_'.$no.'" name="user_updt_'.$no.'" value="'.$row->user_updt.'" />';
								echo'<input type="hidden" id="user_ins_'.$no.'" name="user_ins_'.$no.'" value="'.$row->user_ins.'" />';
								echo'<input type="hidden" id="kode_kantor_'.$no.'" name="kode_kantor_'.$no.'" value="'.$row->kode_kantor.'" />';
							}
							
							
								echo'<tr>';
									echo'<td></td>';
									echo'<td colspan="1" style="text-align:right;font-weight:bold;">TOTAL</td>';
									echo'<td style="text-align:right;font-weight:bold;">'.number_format($subtotalNominal,1,'.',',').'</td>';
									echo'<td style="text-align:right;font-weight:bold;">'.number_format($subTotalBayar,1,'.',',').'</td>';
									echo'<td style="text-align:right;font-weight:bold;">'.number_format($subTotalSisa,1,'.',',').'</td>';
									echo'<td></td>';
								echo'</tr>';
								
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
				/*
				}
				else
				{
					header('Location: '.base_url().'gl-admin-purchase-order-terima');
				}
				*/
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_pst_admin_jurnal_umum.php */