<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class C_gl_admin_upselling extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('M_gl_upselling','M_gl_hitung_gaji','M_tunjangan_karyawan','M_gl_pengaturan'));
        $this->load->library('form_validation');        
	$this->load->library('datatables');
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
                $tgl = date('Y-m-d');

                if((!empty($_GET['tgl_from'])) && ($_GET['tgl_from']!= "")  )
                {
                    $tgl_from = $_GET['tgl_from'];
                    $tgl_to = $_GET['tgl_to'];
                }
                else
                {
                    $tgl_from = date("Y-m-d");
                    $tgl_to = date("Y-m-d");
                }
                
                if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
                {
                    $cari = $_GET['cari'];
                } else {
                    $cari = '';
                }

                $cari_kantor = "WHERE kode_kantor NOT IN('SAL','INV','SLM')";

                $no_upselling = $this->M_gl_upselling->get_no_upselling()->no_upselling;

                $list_upselling = $this->M_gl_upselling->list_upselling($this->session->userdata('ses_kode_kantor'),$tgl_from,$tgl_to,$cari);
                $list_kantor = $this->M_gl_pengaturan->get_data_kantor($cari_kantor)->result();

                $data = array(
                    'page_content'=>'c_gl_admin_upselling/input_upselling_karyawan',
                    'list_upselling' => $list_upselling,
                    'tgl' => $tgl,
                    'no_upselling' => $no_upselling,
                    'tgl_from' => $tgl_from,
                    'tgl_to' => $tgl_to,
                    'cari' => $cari,
                    'list_kantor' => $list_kantor,
                );

                //if($this->session->userdata('ses_akses_lvl2_54') > 0) 
				//{
                    //$this->load->view('pusat/container',$data);
                //} else {
                    $this->load->view('admin/container',$data);
                //}   
            }
            else
            {
                header('Location: '.base_url().'gl-admin-login');
            }
        }
    }

    public function list_produk_upselling()
    {
        $id_h_upselling = $this->input->post('id_h_upselling');

        $data = $this->M_gl_upselling->list_d_upselling($id_h_upselling);

        $no=1;
        foreach ($data as $row) {
            echo '<tr>';
            echo '<td><input type="hidden" id="id_d_upselling_'.$no.'" value="'.$row->id_d_upselling.'" />'.$no.'</td>';
            echo '<td>'.$row->kode_produk.'</td>';
            echo '<td>'.$row->nama_produk.'</td>';
            echo '<td>'.$row->qty.'</td>';
            echo '<td><button class="btn btn-danger" onClick="hapusProduk('.$no.')" >Hapus</button></td>';
            echo '</tr>';

            $no++;
        }
    }



    public function list_karyawan()
    {
      $cari = $this->input->post('cari');
      $kode_kantor = $this->input->post('kode_kantor');

      $data = $this->M_gl_upselling->list_karyawan($cari,$kode_kantor);

      $no=1;
      foreach ($data as $row) {
        echo '<tr>';
        echo '<input type="hidden" id="kode_kantor_'.$no.'" value="'.$row->kode_kantor.'" />';
        echo '<td><input type="hidden" id="id_karyawan_'.$no.'" value="'.$row->id_karyawan.'" />'.$no.'</td>';
        echo '<td><input type="hidden" id="no_karyawan_'.$no.'" value="'.$row->no_karyawan.'" />'.$row->no_karyawan.'</td>';
        echo '<td><input type="hidden" id="nama_karyawan_'.$no.'" value="'.$row->nama_karyawan.'" />'.$row->nama_karyawan.'</td>';
        echo '<td><input type="hidden" id="nama_kantor_'.$no.'" value="'.$row->nama_kantor.'" />'.$row->kode_kantor.'</td>';
        echo '<td><button type="button" class="btn btn-info" data-dismiss="modal" onclick="pilihKaryawan('.$no.')">Pilih</button></td>';
        echo '</tr>';
        $no++;
      }
    }

    public function list_pasien()
    {
        $cari = $this->input->post('cari');
        $kode_kantor = $this->input->post('kode_kantor');

        if($kode_kantor != '')
        {
            $str_cari = " AND kode_kantor = '".$kode_kantor."'";    
        } else {
            $str_cari = '';
        }

        $data = $this->M_gl_upselling->list_pasien($cari,$str_cari);

        $no=1;
        foreach ($data as $row) {
            echo '<tr>';
            echo '<td><input type="hidden" id="id_costumer2_'.$no.'" value="'.$row->id_costumer.'" />'.$no.'</td>';
            echo '<td><input type="hidden" id="no_costumer2_'.$no.'" value="'.$row->no_costumer.'" />'.$row->no_costumer.'</td>';
            echo '<td><input type="hidden" id="nama_costumer2_'.$no.'" value="'.$row->nama_lengkap.'" />'.$row->nama_lengkap.'</td>';
            echo '<td><input type="hidden" id="kode_kantor_cust2_'.$no.'" value="'.$row->kode_kantor.'" />'.$row->kode_kantor.'</td>';
            echo '<td> <button data-dismiss="modal" class="btn btn-info" onClick="pilihPasien('.$no.')"> Pilih </button></td>';
            echo '</tr>';
            $no++;
        }
    } 

    public function list_produk()
    {
        $cari = $this->input->post('cari');

        $data = $this->M_gl_upselling->list_produk($cari);

        $no=1;
        foreach ($data as $row) {
            echo '<tr>';
            echo '<td><input type="hidden" id="id_produk2_'.$no.'" value="'.$row->id_produk.'" />'.$no.'</td>';
            echo '<td><input type="hidden" id="kode_produk2_'.$no.'" value="'.$row->kode_produk.'" />'.$row->kode_produk.'</td>';
            echo '<td><input type="hidden" id="nama_produk2_'.$no.'" value="'.$row->nama_produk.'" />'.$row->nama_produk.'</td>';
            echo '<td><input type="hidden" id="isProduk2_'.$no.'" value="'.$row->isProduk.'" />'.$row->isProduk.'</td>';
            echo '<td><input class="form-control" type="number" id="jumlah2_'.$no.'" value="1" /></td>';
            echo '<td> <button id="btn_ups_'.$no.'" class="btn btn-info" onClick="pilihProduk('.$no.')"> Pilih </button></td>';
            echo '</tr>';
            $no++;
        }
    }

    public function list_produk_edit()
    {
        $cari = $this->input->post('cari');

        $data = $this->M_gl_upselling->list_produk($cari);

        $no=1;
        foreach ($data as $row) {
            echo '<tr>';
            echo '<td><input type="hidden" id="id_produk2_'.$no.'" value="'.$row->id_produk.'" />'.$no.'</td>';
            echo '<td><input type="hidden" id="kode_produk2_'.$no.'" value="'.$row->kode_produk.'" />'.$row->kode_produk.'</td>';
            echo '<td><input type="hidden" id="nama_produk2_'.$no.'" value="'.$row->nama_produk.'" />'.$row->nama_produk.'</td>';
            echo '<td><input type="hidden" id="isProduk2_'.$no.'" value="'.$row->isProduk.'" />'.$row->isProduk.'</td>';
            echo '<td><input class="form-control" type="number" id="jumlah2_'.$no.'" value="1" /></td>';
            echo '<td> <button id="btn_ups_'.$no.'" class="btn btn-info" onClick="pilihProdukEdit('.$no.')"> Pilih </button></td>';
            echo '</tr>';
            $no++;
        }
    }
    
    public function json() {
        header('Content-Type: application/json');
        echo $this->M_gl_upselling->json();
    }

    public function create_action() 
    {

        $id_karyawan = $this->input->post('id_karyawan',TRUE);
        $kode_kantor = $this->session->userdata('ses_kode_kantor');
        $id_h_upselling = $this->input->post('id_h_upselling',TRUE);
        $periode = $this->input->post('periode',TRUE);
        
        if($id_h_upselling == '')
        {
            if(!empty($_FILES['foto']['name']))
            {
              $gen=(string) time();
              $this->do_upload($_FILES['foto']['name'],$gen);
              $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
              $foto = $gen.".".$ext;

              $foto = base_url().'assets/global/upselling/'.$foto;
            } else {
              $foto = '';
            }

            $data = array(
                'tanggal' => $this->input->post('tanggal',TRUE),
                'id_costumer' => $this->input->post('id_costumer',TRUE),
                'no_upselling' => $this->input->post('no_upselling',TRUE),
                'no_hp' => $this->input->post('hp',TRUE),
                'id_karyawan' => $id_karyawan,
                'foto_bukti' => $foto,
                'user_ins' => $this->session->userdata('ses_id_karyawan'),
                'kode_kantor' => $kode_kantor,
                'kode_kantor_cust' => $this->input->post('kode_kantor_cust',TRUE),
           );

            $id = $this->M_gl_upselling->insert($data);

            //INPUT PRODUK

            $det = array(
                    'id_h_upselling' => $id,
                    'id_produk' => $this->input->post('id_produk',TRUE),
                    'qty' => $this->input->post('jumlah',TRUE),
                    'user_ins' => $this->session->userdata('ses_id_karyawan'),
                );

            $this->M_gl_upselling->insert_detail($det);

            echo $id;
        } else {
             $det = array(
                    'id_h_upselling' => $id_h_upselling,
                    'id_produk' => $this->input->post('id_produk',TRUE),
                    'qty' => $this->input->post('jumlah',TRUE),
                    'user_ins' => $this->session->userdata('ses_id_karyawan'),
                );

            $this->M_gl_upselling->insert_detail($det);

            echo $id_h_upselling;
        }
    }

    public function update()
    {
        $id_h_upselling = $this->input->post('id_h_upselling',TRUE);
        
        $det = array(
                'id_h_upselling' => $id_h_upselling,
                'id_produk' => $this->input->post('id_produk',TRUE),
                'qty' => $this->input->post('jumlah',TRUE),
                'user_ins' => $this->session->userdata('ses_id_karyawan'),
            );

        $this->M_gl_upselling->insert_detail($det);

        echo $id_h_upselling;
    }

    
    public function delete() 
    {
        $id = $this->uri->segment(3,0);

        $row = $this->M_gl_upselling->get_by_id($id);

        if ($row) {
            $this->M_gl_upselling->delete($id);
            $this->M_gl_upselling->delete_detail($id);
            $this->session->set_flashdata('message', 'Delete Record Success');
            redirect(site_url('gl-admin-upselling'));
        } else {
            $this->session->set_flashdata('message', 'Record Not Found');
            redirect(site_url('gl-admin-upselling'));
        }
    }

    public function delete_detail()
    {
        $id = $this->input->post('id_d_upselling');
        $this->M_gl_upselling->delete_detail_satuan($id);
    }

    public function _rules() 
    {
	$this->form_validation->set_rules('tanggal', 'tanggal', 'trim|required');
	$this->form_validation->set_rules('id_costumer', 'id costumer', 'trim|required');
	$this->form_validation->set_rules('id_produk', 'id produk', 'trim|required');
	$this->form_validation->set_rules('foto_bukti', 'foto_bukti', 'trim|required');
	$this->form_validation->set_rules('tgl_ins', 'tgl ins', 'trim|required');
	$this->form_validation->set_rules('tgl_updt', 'tgl updt', 'trim|required');
	$this->form_validation->set_rules('user_ins', 'user ins', 'trim|required');
	$this->form_validation->set_rules('user_updt', 'user updt', 'trim|required');
	$this->form_validation->set_rules('kode_kantor', 'kode kantor', 'trim|required');

	$this->form_validation->set_rules('id_h_upselling', 'id_h_upselling', 'trim');
	$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }

    public function excel()
    {
        $this->load->helper('exportexcel');
        $namaFile = "tb_h_upselling.xls";
        $judul = "tb_h_upselling";
        $tablehead = 0;
        $tablebody = 1;
        $nourut = 1;
        //penulisan header
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename=" . $namaFile . "");
        header("Content-Transfer-Encoding: binary ");

        xlsBOF();

        $kolomhead = 0;
        xlsWriteLabel($tablehead, $kolomhead++, "No");
	xlsWriteLabel($tablehead, $kolomhead++, "Tanggal");
	xlsWriteLabel($tablehead, $kolomhead++, "Id Costumer");
	xlsWriteLabel($tablehead, $kolomhead++, "Id Produk");
	xlsWriteLabel($tablehead, $kolomhead++, "foto_bukti");
	xlsWriteLabel($tablehead, $kolomhead++, "Tgl Ins");
	xlsWriteLabel($tablehead, $kolomhead++, "Tgl Updt");
	xlsWriteLabel($tablehead, $kolomhead++, "User Ins");
	xlsWriteLabel($tablehead, $kolomhead++, "User Updt");
	xlsWriteLabel($tablehead, $kolomhead++, "Kode Kantor");

	foreach ($this->M_gl_upselling->get_all() as $data) {
            $kolombody = 0;

            //ubah xlsWriteLabel menjadi xlsWriteNumber untuk kolom numeric
            xlsWriteNumber($tablebody, $kolombody++, $nourut);
	    xlsWriteLabel($tablebody, $kolombody++, $data->tanggal);
	    xlsWriteLabel($tablebody, $kolombody++, $data->id_costumer);
	    xlsWriteLabel($tablebody, $kolombody++, $data->id_produk);
	    xlsWriteLabel($tablebody, $kolombody++, $data->foto_bukti);
	    xlsWriteLabel($tablebody, $kolombody++, $data->tgl_ins);
	    xlsWriteLabel($tablebody, $kolombody++, $data->tgl_updt);
	    xlsWriteLabel($tablebody, $kolombody++, $data->user_ins);
	    xlsWriteLabel($tablebody, $kolombody++, $data->user_updt);
	    xlsWriteLabel($tablebody, $kolombody++, $data->kode_kantor);

	    $tablebody++;
            $nourut++;
        }

        xlsEOF();
        exit();
    }

    function do_upload($id,$cek_bfr)
    {
        $this->load->library('upload');

        if($cek_bfr != '')
        {
            @unlink('./assets/global/upselling/'.$cek_bfr);
        }

        if (!empty($_FILES['foto']['name']))
        {
            $config['upload_path'] = 'assets/global/upselling/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size'] = '5000';
            //$config['max_widtd']  = '300';
            //$config['max_height']  = '300';
            $config['file_name']    = $cek_bfr;
            $config['overwrite']    = true;


            $this->upload->initialize($config);

            //Upload file 1
            if ($this->upload->do_upload('foto'))
            {
                $hasil = $this->upload->data();
            }
        }
    }

}

/* End of file C_gl_admin_upselling.php */
/* Location: ./application/controllers/C_gl_admin_upselling.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2020-12-07 05:12:52 */
/* http://harviacode.com */