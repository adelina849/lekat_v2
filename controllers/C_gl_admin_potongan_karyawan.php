<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class C_gl_admin_potongan_karyawan extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('M_potongan_karyawan','M_akun','M_gl_hitung_gaji',''));
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

            $id_karyawan = $this->uri->segment(2,0);
            $kode_kantor = $this->uri->segment(3,0);

            $header = $this->M_gl_hitung_gaji->get_karyawan($id_karyawan,$kode_kantor);
            $list_potongan = $this->M_potongan_karyawan->list_potongan_karyawan($id_karyawan);

            $data = array(
                    'page_content'=>'c_gl_admin_potongan_karyawan/input_potongan_karyawan',
                    'header'=>$header,
                    'id_karyawan' => $id_karyawan,
                    'list_potongan' => $list_potongan,
                    'kode_kantor' => $kode_kantor,
                  );
            $this->load->view('pusat/container',$data);
          }
          else
          {
            header('Location: '.base_url().'gl-admin-login');
          }
        }
    }

    public function json() {
        header('Content-Type: application/json');
        echo $this->M_potongan_karyawan->json();
    }

    public function list_potongan()
    {
      $cari = $this->input->post('cari');
      $data = $this->M_gl_potongan->get_like($cari);

      $no=1;
      foreach ($data as $row) {
        echo '<tr>';
        echo '<td><input type="hidden" id="id_potongan_'.$no.'" value="'.$row->id_potongan.'" />'.$no.'</td>';
        echo '<td><input type="hidden" id="nama_potongan_'.$no.'" value="'.$row->nama_potongan.'" />'.$row->nama_potongan.'</td>';
        echo '<td><button type="button" class="btn btn-info" data-dismiss="modal" onclick="pilihpotongan('.$no.')">Pilih</button></td>';
        echo '</tr>';
        $no++;
      }

    }

    public function set_default()
    {
      $id_karyawan = $this->input->post('id_karyawan');
      $kode_kantor = $this->input->post('kode_kantor');
      $user_ins = $this->session->userdata('ses_id_karyawan');

      $this->M_potongan_karyawan->delete_potongan($id_karyawan,$kode_kantor);
      $this->M_potongan_karyawan->set_default($id_karyawan,$user_ins,$kode_kantor);

    }

    public function create()
    {

        $data = array(
            'button' => 'Create',
            'action' => site_url('c_gl_admin_potongan_karyawan/create_action'),
      	    'id_potongan_karyawan' => set_value('id_potongan_karyawan'),
            'id_potongan' => set_value('id_potongan'),
      	    'id_karyawan' => set_value('id_karyawan'),
            'kode_kantor' => set_value('kode_kantor'),
            'nama_karyawan' => '',
            'nama_potongan' => '',
      	    'nominal' => set_value('nominal'),
            'page_content'=>'c_gl_admin_potongan_karyawan/gl_admin_input_potongan_karyawan2',
            'title' => 'Input Potongan Karyawan',
      	);
        $this->load->view('pusat/container',$data);
    }

    public function create_action()
    {
        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->create();
        } else {
            $data = array(
              'id_potongan' => $this->input->post('id_potongan',TRUE),
          		'id_karyawan' => $this->input->post('id_karyawan',TRUE),
              'kode_kantor' => $this->input->post('kode_kantor',TRUE),
          		'nominal' => $this->input->post('nominal',TRUE),
          		'is_aktif' => '0',
              'user_ins' => $this->session->userdata('ses_id_karyawan'),
              'kode_kantor' => $this->input->post('kode_kantor',TRUE),
	          );

            $this->M_potongan_karyawan->insert($data);
            $this->session->set_flashdata('message', 'Create Record Success');
            redirect(site_url('gl-admin-potongan-karyawan'));
        }
    }

    public function update()
    {
        $id = $this->uri->segment(2,0);
        $row = $this->M_potongan_karyawan->get_by_id($id);

        if ($row) {
            $data = array(
                'button' => 'Update',
                'action' => site_url('c_gl_admin_potongan_karyawan/update_action'),
            		'id_potongan_karyawan' => set_value('id_potongan_karyawan', $row->id_potongan_karyawan),
                'id_potongan' => set_value('id_potongan', $row->id_potongan),
            		'id_karyawan' => set_value('id_karyawan', $row->id_karyawan),
                'kode_kantor' => set_value('kode_kantor', $row->kode_kantor),
                'nama_karyawan' => set_value('nama_karyawan', $row->nama_karyawan),
                'nama_potongan' => set_value('nama_potongan', $row->nama_potongan),
            		'nominal' => set_value('nominal', $row->nominal),
            		'is_aktif' => set_value('is_aktif', $row->is_aktif),
                'page_content'=>'c_gl_admin_potongan_karyawan/gl_admin_input_potongan_karyawan2',
                'title' => 'Update potongan Karyawan',
	          );
            $this->load->view('pusat/container',$data);
        } else {
            $this->session->set_flashdata('message', 'Record Not Found');
            redirect(site_url('gl-admin-potongan-karyawan'));
        }
    }

    public function setaktif_potongan()
    {
      $id_potongan_karyawan = $this->input->post('id_potongan_karyawan');
      $id_potongan = $this->input->post('id_potongan');
      $is_aktif = $this->input->post('is_aktif');
      $id_karyawan = $this->input->post('id_karyawan');
      $kode_kantor = $this->input->post('kode_kantor');
      $tgl = date('Y-m-d H:i:s');

      if($id_potongan_karyawan == '')
      {
        $data = array(
              'id_potongan' => $id_potongan,
              'id_karyawan' => $id_karyawan,
              'kode_kantor' => $kode_kantor,
              'nominal' => '0',
              'is_aktif' => '0',
              'kode_kantor' => $kode_kantor,
              'user_ins' => $this->session->userdata('ses_id_karyawan'),
            );

            $this->M_potongan_karyawan->insert($data);
      } else {

        if($is_aktif == '0')
        {
          $data = array(
            'is_aktif' => '1',
            'tgl_updt' => $tgl,
            'user_updt' => $this->session->userdata('ses_id_karyawan'),
          );

          $this->M_potongan_karyawan->update($id_potongan_karyawan, $data);  
        } else {
          $data = array(
            'is_aktif' => '0',
            'tgl_updt' => $tgl,
            'user_updt' => $this->session->userdata('ses_id_karyawan'),
          );

          $this->M_potongan_karyawan->update($id_potongan_karyawan, $data);  
        }
        
      }
    }

    public function update_action()
    {
        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->update($this->input->post('id_potongan_karyawan', TRUE));
        } else {
            $data = array(
          		'id_karyawan' => $this->input->post('id_karyawan',TRUE),
          		'nominal' => $this->input->post('nominal',TRUE),
              'tgl_updt' => $tgl,
          		'user_updt' => $this->session->userdata('ses_id_karyawan'),
	           );

            $this->M_potongan_karyawan->update($this->input->post('id_potongan_karyawan', TRUE), $data);
            $this->session->set_flashdata('message', 'Update Record Success');
            redirect(site_url('gl-admin-potongan-karyawan'));
        }
    }

    public function update_potongan()
    {
       
       $tgl = date('Y-m-d H:i:s');
       $data = array(
          'nominal' => $this->input->post('nominal',TRUE),
          'tgl_updt' => $tgl,
          'user_updt' => $this->session->userdata('ses_id_karyawan'),
        );

        $this->M_potongan_karyawan->update($this->input->post('id_potongan_karyawan', TRUE), $data);
    }

    public function delete($id)
    {
        //$row = $this->M_potongan_karyawan->get_by_id($id);

        //if ($row) {
            $this->M_potongan_karyawan->delete($id);
            $this->session->set_flashdata('message', 'Delete Record Success');
            redirect(site_url('gl-admin-potongan-karyawan'));
        //} else {
       //     $this->session->set_flashdata('message', 'Record Not Found');
       //     redirect(site_url('gl-admin-potongan-karyawan'));
       // }
    }

    public function _rules()
    {
    	$this->form_validation->set_rules('id_karyawan', 'id karyawan', 'trim|required');
      $this->form_validation->set_rules('id_potongan', 'id potongan', 'trim|required');
    	$this->form_validation->set_rules('nominal', 'nominal', 'trim|required');

    	$this->form_validation->set_rules('id_potongan_karyawan', 'id_potongan_karyawan', 'trim');
    	$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }

}

/* End of file C_gl_admin_potongan_karyawan.php */
/* Location: ./application/controllers/C_gl_admin_potongan_karyawan.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2020-10-08 06:45:10 */
/* http://harviacode.com */
