<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class C_gl_admin_gaji_pokok extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('M_gl_gaji_pokok','M_akun'));
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

            $data = array('page_content'=>'c_gl_admin_gaji_pokok/gl_admin_gapok');
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
        echo $this->M_gl_gaji_pokok->json();
    }

    public function create()
    {
        $data = array(
            'button' => 'Create',
            'action' => site_url('c_gl_admin_gaji_pokok/create_action'),
      	    'id_gaji_pokok' => set_value('id_gaji_pokok'),
      	    'id_karyawan' => set_value('id_karyawan'),
            'is_pajak' => set_value('is_pajak'),
            'ptkp' => set_value('ptkp'),
            'kode_kantor' => set_value('kode_kantor'),
            'nama_karyawan' => '',
      	    'tanggal' => set_value('tanggal'),
      	    'besar_gaji' => set_value('besar_gaji'),
            'rumus_gaji' => set_value('rumus_gaji'),
            'page_content'=>'c_gl_admin_gaji_pokok/gl_admin_input_gapok',
            'title' => 'Input Gaji Pokok',
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
          		'id_karyawan' => $this->input->post('id_karyawan',TRUE),
          		'tanggal' => $this->input->post('tanggal',TRUE),
          		'besar_gaji' => $this->input->post('besar_gaji',TRUE),
                'is_pajak' => $this->input->post('is_pajak',TRUE),
                'ptkp' => $this->input->post('ptkp',TRUE),
                'rumus_gaji' => $this->input->post('rumus_gaji',TRUE),
                'kode_kantor' => $this->input->post('kode_kantor',TRUE),
                'user_ins' => $this->session->userdata('ses_id_karyawan'),
                'kode_kantor' => $this->input->post('kode_kantor',TRUE),
	          );

            $this->M_gl_gaji_pokok->insert($data);
            $this->session->set_flashdata('message', 'Create Record Success');
            redirect(site_url('gl-admin-gapok'));
        }
    }

    public function update()
    {
        $id = $this->uri->segment(2,0);
        $row = $this->M_gl_gaji_pokok->get_by_id($id);

        if ($row) {
            $data = array(
                'button' => 'Update',
                'action' => site_url('c_gl_admin_gaji_pokok/update_action'),
            	'id_gaji_pokok' => set_value('id_gaji_pokok', $row->id_gaji_pokok),
            	'id_karyawan' => set_value('id_karyawan', $row->id_karyawan),
                'is_pajak' => set_value('is_pajak', $row->is_pajak),
                'ptkp' => set_value('ptkp', $row->ptkp),
                'rumus_gaji' => set_value('rumus_gaji', $row->rumus_gaji),
                'kode_kantor' => set_value('kode_kantor', $row->kode_kantor),
                'nama_karyawan' => set_value('nama_karyawan', $row->nama_karyawan),
            	'tanggal' => set_value('tanggal', $row->tanggal),
            	'besar_gaji' => set_value('besar_gaji', $row->besar_gaji),
                'page_content'=>'c_gl_admin_gaji_pokok/gl_admin_input_gapok',
                'title' => 'Update Gaji Pokok',
	          );
            $this->load->view('pusat/container',$data);
        } else {
            $this->session->set_flashdata('message', 'Record Not Found');
            redirect(site_url('gl-admin-gapok'));
        }
    }

    public function update_action()
    {
        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->update($this->input->post('id_gaji_pokok', TRUE));
        } else {
            $tgl = date('Y-m-d H:i:s');
            $data = array(
          		'id_karyawan' => $this->input->post('id_karyawan',TRUE),
                'is_pajak' => $this->input->post('is_pajak',TRUE),
                'ptkp' => $this->input->post('ptkp',TRUE),
                'rumus_gaji' => $this->input->post('rumus_gaji',TRUE),
                'kode_kantor' => $this->input->post('kode_kantor',TRUE),
          		'tanggal' => $this->input->post('tanggal',TRUE),
          		'besar_gaji' => $this->input->post('besar_gaji',TRUE),
                'tgl_updt' => $tgl,
          		'user_updt' => $this->session->userdata('ses_id_karyawan'),
	           );

            $this->M_gl_gaji_pokok->update($this->input->post('id_gaji_pokok', TRUE), $data);
            $this->session->set_flashdata('message', 'Update Record Success');
            redirect(site_url('gl-admin-gapok'));
        }
    }

    public function delete($id)
    {
        $row = $this->M_gl_gaji_pokok->get_by_id($id);

        if ($row) {
            $this->M_gl_gaji_pokok->delete($id);
            $this->session->set_flashdata('message', 'Delete Record Success');
            redirect(site_url('gl-admin-gapok'));
        } else {
            $this->session->set_flashdata('message', 'Record Not Found');
            redirect(site_url('gl-admin-gapok'));
        }
    }

    public function _rules()
    {
    	$this->form_validation->set_rules('id_karyawan', 'id karyawan', 'trim|required');
    	//$this->form_validation->set_rules('tanggal', 'tanggal', 'trim|required');
    	$this->form_validation->set_rules('besar_gaji', 'besar gaji', 'trim|required');

    	$this->form_validation->set_rules('id_gaji_pokok', 'id_gaji_pokok', 'trim');
    	$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }

    public function excel()
    {
        $this->load->helper('exportexcel');
        $namaFile = "tb_gaji_pokok.xls";
        $judul = "tb_gaji_pokok";
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
    	xlsWriteLabel($tablehead, $kolomhead++, "Id Karyawan");
    	xlsWriteLabel($tablehead, $kolomhead++, "Tanggal");
    	xlsWriteLabel($tablehead, $kolomhead++, "Besar Gaji");
    	xlsWriteLabel($tablehead, $kolomhead++, "Tgl Ins");
    	xlsWriteLabel($tablehead, $kolomhead++, "Tgl Updt");
    	xlsWriteLabel($tablehead, $kolomhead++, "User Ins");
    	xlsWriteLabel($tablehead, $kolomhead++, "User Updt");

	   foreach ($this->M_gl_gaji_pokok->get_all() as $data) {
            $kolombody = 0;

            //ubah xlsWriteLabel menjadi xlsWriteNumber untuk kolom numeric
            xlsWriteNumber($tablebody, $kolombody++, $nourut);
    	    xlsWriteLabel($tablebody, $kolombody++, $data->id_karyawan);
    	    xlsWriteLabel($tablebody, $kolombody++, $data->tanggal);
    	    xlsWriteLabel($tablebody, $kolombody++, $data->besar_gaji);
    	    xlsWriteLabel($tablebody, $kolombody++, $data->tgl_ins);
    	    xlsWriteLabel($tablebody, $kolombody++, $data->tgl_updt);
    	    xlsWriteLabel($tablebody, $kolombody++, $data->user_ins);
    	    xlsWriteLabel($tablebody, $kolombody++, $data->user_updt);

	        $tablebody++;
            $nourut++;
        }

        xlsEOF();
        exit();
    }

}

/* End of file C_gl_admin_gaji_pokok.php */
/* Location: ./application/controllers/C_gl_admin_gaji_pokok.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2020-10-08 06:45:39 */
/* http://harviacode.com */
