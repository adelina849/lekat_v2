<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_gl_hitung_gaji extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    function closing_gaji($data)
    {
      $this->db->insert('tb_closing',$data);
    }

    function cek_closing($periode)
    {
      $data = $this->db->query("SELECT stat_closing
                                FROM tb_closing
                                WHERE periode = '".$periode."'")->row();
      if(empty($data))
      {
        return '0';
      } else {
        return $data->stat_closing;
      }
    }

    function get_data_kembalian_pajak($id_karyawan,$periode)
    {
      $query = $this->db->query("
          SELECT nominal 
          FROM tb_d_payment
          WHERE kode_akun IN('KOPERASI') AND id_karyawan = '".$id_karyawan."' AND periode = '".$periode."'
                AND jenis_akun = 'KREDIT' 
        ")->row();

      if(empty($query))
      {
        return 0;
      } else {
        return $query->nominal;  
      }
    }

    function cek_masa_kerja($id_karyawan,$kode_kantor)
    {
      $data = $this->db->query("
        SELECT DATEDIFF(NOW(),tgl_diterima) AS masa_kerja
        FROM tb_karyawan
        WHERE id_karyawan = '".$id_karyawan."' AND kode_kantor = '".$kode_kantor."'
      ")->row();
    }

    function list_tunjangan_karyawan($id_karyawan,$kode_kantor,$periode)
    {
      $data = $this->db->query("
          SELECT id_tunjangan_karyawan,B.kode_tunjangan,C.id_tampung,B.nama_tunjangan,COALESCE(C.keterangan,'') as keterangan,
            CASE WHEN B.kode_tunjangan NOT IN('KONSUL1','KONSUL2','LEMBUR','TINDAKAN','UPSELLING','UM','LIBUR','KENAIKAN','ASSIST') THEN
              COALESCE(C.nominal,A.nominal) ELSE COALESCE(C.nominal,0) END AS nominal,is_aktif
          FROM tb_tunjangan_karyawan A
          LEFT JOIN tb_tunjangan B
            ON A.id_tunjangan = B.id_tunjangan
          LEFT JOIN tb_d_tampung_gaji C
            ON B.kode_tunjangan = C.kode_akun AND A.id_karyawan = C.id_karyawan 
               AND C.periode = '".$periode."'
          WHERE A.id_karyawan = '".$id_karyawan."'
          ORDER BY B.nama_tunjangan
      ")->result();

      return $data;
    }

    function list_potongan_karyawan($id_karyawan,$kode_kantor,$periode)
    {
      $data = $this->db->query("
          SELECT A.id_potongan_karyawan,C.id_tampung,nama_potongan,B.kode_potongan,B.nama_potongan,COALESCE(C.keterangan,'') as keterangan,
            CASE WHEN B.kode_potongan NOT IN('LATE','ABSEN','PUNISH') THEN
              COALESCE(C.nominal,A.nominal) ELSE COALESCE(C.nominal,0) END AS nominal,is_aktif
          FROM tb_potongan_karyawan A
          LEFT JOIN tb_potongan B
            ON A.id_potongan = B.id_potongan
          LEFT JOIN tb_d_tampung_gaji C
            ON B.kode_potongan = C.kode_akun AND A.id_karyawan = C.id_karyawan -- AND A.kode_kantor = C.kode_kantor
               AND C.periode = '".$periode."'
          WHERE A.id_karyawan = '".$id_karyawan."' -- AND A.kode_kantor = '".$kode_kantor."'
          ORDER BY B.nama_potongan
      ")->result();

      return $data;
    }

    function get_karyawan($id_karyawan,$kode_kantor)
    {
      $data = $this->db->query("
          SELECT A.id_karyawan,no_karyawan,nama_karyawan,nama_kantor,A.kode_kantor,D.nama_jabatan,rumus_gaji
          FROM tb_karyawan A
          LEFT JOIN tb_karyawan_jabatan B
            ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
          left join tb_jabatan D
            ON B.id_jabatan = D.id_jabatan AND B.kode_kantor = D.kode_kantor
          LEFT JOIN tb_kantor C
            ON A.kode_kantor = C.kode_kantor
          left join tb_gaji_pokok G
            on A.id_karyawan = G.id_karyawan
          WHERE A.id_karyawan = '".$id_karyawan."' and A.kode_kantor = '".$kode_kantor."'
      ")->row();

      return $data;
    }

    function list_karyawan($cari)
    {
      $data = $this->db->query("
          SELECT A.*,D.nama_jabatan,DATEDIFF(NOW(),tgl_diterima) / 365 AS masa_kerja,B.is_pajak,COALESCE(B.ptkp,0) as ptkp
          FROM tb_karyawan A
          LEFT JOIN tb_gaji_pokok B
            ON A.id_karyawan = B.id_karyawan -- AND A.kode_kantor = B.kode_kantor
          LEFT JOIN tb_karyawan_jabatan C
            ON A.id_karyawan = C.id_karyawan AND A.kode_kantor = C.kode_kantor
          LEFT JOIN tb_jabatan D
            ON C.id_jabatan = D.id_jabatan AND C.kode_kantor = D.kode_kantor
          WHERE B.id_karyawan IS NOT NULL AND isDefault = '1' -- AND A.isAktif = 'DITERIMA'
          AND nama_karyawan LIKE '%".$cari."%'
      ")->result();

      return $data;
    }

    function list_dokter()
    {
      $data = $this->db->query("
          SELECT A.*
          FROM tb_karyawan A
          LEFT JOIN tb_karyawan_jabatan C
            ON A.id_karyawan = C.id_karyawan AND A.kode_kantor = C.kode_kantor
          WHERE isDefault = '1' AND isDokter = 'DOKTER' 
      ")->result();

      return $data;
    }

    function hitung_upselling($tgl_from,$tgl_to,$periode)
    {
      $data = $this->db->query("
        INSERT INTO tb_d_tampung_gaji (
            id_karyawan,
            periode,
            kode_akun,
            nama_akun,
            jenis_akun,
            keterangan,
            nominal,
            user_ins,
            kode_kantor
          )
          SELECT A.id_karyawan,
                 '".$periode."' AS periode,
                 'UPSELLING' AS kode_akun,
                 'Fee Upselling' AS nama_akun,
                 'DEBIT' AS jenis_akun,
                 '' keterangan,
                 SUM(fee_upselling) fee_upselling,
                 '".$this->session->userdata('ses_id_karyawan')."' user_ins, 
                 A.kode_kantor
          FROM 
          (
            SELECT id_karyawan,kode_kantor,fee_upselling * qty AS fee_upselling
            FROM 
            (  
             SELECT  A.id_karyawan,A.kode_kantor,CASE WHEN B.isProduk = 'JASA' THEN
               CASE WHEN tgl_h_penjualan IS NOT NULL THEN E.harga * 0.01
              ELSE C.harga * 0.01 
              END
             ELSE 5000 END AS fee_upselling,P.id_produk,P.qty,B.nama_produk
              FROM tb_h_upselling A
              LEFT JOIN tb_d_upselling P
            ON A.id_h_upselling = P.id_h_upselling
              LEFT JOIN tb_produk B
            ON B.id_produk = P.id_produk AND B.kode_kantor = 'JKT'
              LEFT JOIN tb_produk_harga_satuan_costumer C
            ON B.id_produk = C.id_produk AND C.kode_kantor = 'JKT' AND C.media = 'KLINIK' AND C.id_costumer = 'ONJKT2020060200002'
            LEFT JOIN tb_satuan_konversi S
              ON C.id_produk = S.id_produk AND C.kode_kantor = S.kode_kantor AND C.id_satuan = S.id_satuan
            LEFT JOIN
              (
              SELECT tgl_h_penjualan,id_produk,(A.harga - (A.harga * A.diskon / 100)) * A.jumlah AS harga,A.kode_kantor,nama_diskon
              FROM tb_d_penjualan A
              LEFT JOIN tb_h_penjualan B
              ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
              LEFT JOIN tb_h_diskon C
              ON A.id_h_diskon = C.id_h_diskon AND A.kode_kantor = C.kode_kantor
              WHERE B.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
              AND A.id_h_diskon <> '' AND isProduk = 'JASA' 
              ) E ON A.tanggal = E.tgl_h_penjualan AND B.id_produk = E.id_produk AND A.kode_kantor = E.kode_kantor
              WHERE tanggal BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND B.isProduk IN('JASA','PRODUK') AND COALESCE(foto_bukti,'') <> '' AND besar_konversi = '1'
            ) A 
            ) A GROUP BY A.id_karyawan,A.kode_kantor
      ");
    }

    function hitung_uang_makan($tgl_from,$tgl_to,$periode)
    {
        $this->db->query("
          INSERT INTO tb_d_tampung_gaji (
              id_karyawan,
              periode,
              kode_akun,
              nama_akun,
              jenis_akun,
              keterangan,
              nominal,
              user_ins,
              kode_kantor
            )
          SELECT A.id_karyawan,'".$periode."' AS periode,'UM' AS kode_akun,'Uang Makan' AS nama_akun,'DEBIT' AS jenis_akun,
                 '' AS ket,SUM(nominal) AS nominal ,'".$this->session->userdata('ses_id_karyawan')."' AS user_ins,'' AS kode_kantor
          FROM 
          (  
            SELECT A.id_karyawan,A.tanggal,COALESCE(B.nominal,0) AS nominal
            FROM
            (
              SELECT id_karyawan,tanggal
              FROM tb_jam_kerja_karyawan A
              WHERE nama_jam NOT IN('LIBUR','CUTI','Izin','Sakit SKD','Sakit Tanpa SKD','Absen','Libur Hari Raya','Libur HR Potong','Dirumahkan','Lembur OFF')
              AND tanggal BETWEEN '".$tgl_from."' AND '".$tgl_to."'
            ) A LEFT JOIN(
              SELECT A.id_karyawan,A.nominal
              FROM tb_tunjangan_karyawan A
              LEFT JOIN tb_tunjangan B
                 ON A.id_tunjangan = B.id_tunjangan
              WHERE kode_tunjangan = 'UM'
            ) B ON A.id_karyawan = B.id_karyawan
          ) A
          GROUP BY A.id_karyawan
        ");
    }

    function hitung_punishment($tgl_from,$tgl_to,$periode)
    {
        $this->db->query("
            INSERT INTO tb_d_tampung_gaji (
              id_karyawan,
              periode,
              kode_akun,
              nama_akun,
              jenis_akun,
              keterangan,
              nominal,
              user_ins,
              kode_kantor
                  )
            SELECT id_karyawan,'".$periode."' periode,'PUNISH' AS kode_akun,'Pelanggaran' AS nama_akun,
              'KREDIT' AS jenis_akun,'' AS ket,SUM(CASE WHEN hukuman = 'SPLISAN' THEN '100000'
                  WHEN hukuman = 'SP1' THEN '300000'
                  WHEN hukuman = 'SP2' THEN '500000' 
                  WHEN hukuman = 'NOTE' THEN 20000 * qty
                  ELSE 0  END) AS potongan,
              '' AS user_ins,'' AS kode_kantor
            FROM tb_punish
            WHERE tgl_mulai BETWEEN '".$tgl_from."' AND '".$tgl_to."'
            GROUP BY id_karyawan
        ");
    }

    function detail_punishment($id_karyawan,$tgl_from,$tgl_to)
    {
        $data = $this->db->query("
          SELECT tgl_mulai,tgl_selesai,nama_pelanggaran,hukuman,
            CASE WHEN hukuman = 'SPLISAN' THEN '100000'
              WHEN hukuman = 'SP1' THEN '300000'
              WHEN hukuman = 'SP2' THEN '500000' 
              WHEN hukuman = 'NOTE' THEN 20000 * qty
              ELSE 0  END AS potongan
          FROM tb_punish
          WHERE tgl_mulai BETWEEN '".$tgl_from."' AND '".$tgl_to."'
          AND id_karyawan = '".$id_karyawan."'
        ")->result();

        return $data;
    }

    function hitung_produk_lebih($tgl_from,$tgl_to,$periode)
    {
      $data = $this->db->query("
          INSERT INTO tb_d_tampung_gaji (
                id_karyawan,
                periode,
                kode_akun,
                nama_akun,
                jenis_akun,
                keterangan,
                nominal,
                user_ins,
                kode_kantor
              )
          SELECT id_dokter,'".$periode."' periode,'LEBIH1' AS kode_akun,'Ins Produk Pasien > 1jt' AS nama_akun,
                'DEBIT' AS jenis_akun,'' AS ket,SUM(ROUND(fee)) AS nominal ,'' AS user_ins,'' AS kode_kantor 
          FROM
          (
          SELECT id_dokter,subtotal,fee
          FROM (
            SELECT id_dokter,nama_karyawan,no_faktur,tgl_h_penjualan,SUM(subtotal) AS subtotal,SUM(subtotal) * 0.05 AS fee
            FROM ( 
             SELECT id_dokter,nama_karyawan,nama_costumer,no_faktur,tgl_h_penjualan,jumlah_konversi * (harga_konversi - diskon_konversi) AS subtotal
              FROM (
                 SELECT A.id_dokter,D.nama_karyawan,A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,
                 A.kode_kantor,E.isProduk,DATEDIFF(tgl_h_penjualan,D.tgl_diterima)/365 AS masa_kerja,B.id_h_diskon,nama_diskon,
                 CASE WHEN B.status_konversi = '*' THEN
                    B.jumlah * B.konversi
                  ELSE
                    B.jumlah / B.konversi
                  END AS jumlah_konversi
                  
                  ,CASE WHEN B.status_konversi = '*' THEN
                    (B.harga / 1.1) / B.konversi
                  ELSE
                    B.harga % B.konversi
                  END AS harga_konversi
                  
                  ,CASE WHEN B.optr_diskon = '%' THEN
                    ((B.harga / 1.1)/100) * B.diskon
                  ELSE
                    B.diskon
                  END AS diskon_konversi,B.optr_diskon,B.diskon
                FROM tb_h_penjualan A
                LEFT JOIN tb_d_penjualan B
                  ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
                LEFT JOIN tb_costumer C 
                  ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
                LEFT JOIN tb_karyawan D
                  ON A.id_dokter = D.id_karyawan AND A.kode_kantor = D.kode_kantor
                LEFT JOIN tb_produk E
                  ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
                LEFT JOIN tb_h_diskon F
                  ON A.id_h_diskon = F.id_h_diskon AND B.kode_kantor = F.kode_kantor
                WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
                  AND A.id_dokter <> '' AND type_h_penjualan IN('KONSULTASI KECANTIKAN')
                  AND sts_penjualan IN ('SELESAI','PEMBAYARAN')
                  AND E.isProduk IN('PRODUK') AND DATEDIFF(A.tgl_h_penjualan,tgl_diterima) >= 730
                  AND E.nama_produk NOT IN('PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','MATERAI','SPON','SPREI+SPONS')
                ) A 
             ) A
             GROUP BY id_dokter,nama_karyawan,no_faktur,tgl_h_penjualan
          ) A WHERE subtotal >= 1100000
          ) A GROUP BY id_dokter

      ");

    }

    function hitung_tindakan_lebih($tgl_from,$tgl_to,$periode)
    {
      $data = $this->db->query("
          INSERT INTO tb_d_tampung_gaji (
                  id_karyawan,
                  periode,
                  kode_akun,
                  nama_akun,
                  jenis_akun,
                  keterangan,
                  nominal,
                  user_ins,
                  kode_kantor
                )
            SELECT id_dokter,'".$periode."' periode,'LEBIH10' AS kode_akun,'Treatment > 10jt' AS nama_akun,'DEBIT' AS jenis_akun,
                    '' AS ket,COUNT(subtotal) * 150000 AS nominal,'".$this->session->userdata('ses_id_karyawan')."' AS user_ins,'' AS kode_kantor 
            FROM (
              SELECT id_dokter,nama_karyawan,nama_costumer,tgl_h_penjualan,SUM(ROUND(subtotal)) AS subtotal 
              FROM ( 
               SELECT id_dokter,nama_karyawan,nama_costumer,no_faktur,tgl_h_penjualan,jumlah_konversi * (harga_konversi - diskon_konversi) AS subtotal
                FROM (
                   SELECT A.id_dokter,D.nama_karyawan,A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,
                   A.kode_kantor,E.isProduk,DATEDIFF(tgl_h_penjualan,D.tgl_diterima)/365 AS masa_kerja,B.id_h_diskon,nama_diskon,
                   CASE WHEN B.status_konversi = '*' THEN
                      B.jumlah * B.konversi
                    ELSE
                      B.jumlah / B.konversi
                    END AS jumlah_konversi
                    
                    ,CASE WHEN B.status_konversi = '*' THEN
                      B.harga / B.konversi
                    ELSE
                      B.harga % B.konversi
                    END AS harga_konversi
                    
                    ,CASE WHEN B.optr_diskon = '%' THEN
                      (B.harga/100) * B.diskon
                    ELSE
                      B.diskon
                    END AS diskon_konversi,B.optr_diskon,B.diskon
                  FROM tb_h_penjualan A
                  LEFT JOIN tb_d_penjualan B
                    ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
                  LEFT JOIN tb_costumer C 
                    ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
                  LEFT JOIN tb_karyawan D
                    ON A.id_dokter = D.id_karyawan AND A.kode_kantor = D.kode_kantor
                  LEFT JOIN tb_produk E
                    ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
                  LEFT JOIN tb_h_diskon F
                    ON A.id_h_diskon = F.id_h_diskon AND B.kode_kantor = F.kode_kantor
                  WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
                    AND A.id_dokter <> '' AND type_h_penjualan IN('KONSULTASI KECANTIKAN')
                    AND sts_penjualan IN ('SELESAI','PEMBAYARAN')
                    AND E.isProduk IN('JASA') AND LOCATE('INFUS CROMOSOM',E.nama_produk) = 0
                    AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD')
                  ) A 
               ) A GROUP BY id_dokter,nama_karyawan,nama_costumer,tgl_h_penjualan
            ) A WHERE subtotal >= 11000000
            GROUP BY id_dokter
      ");
    }

    function hitung_hari_libur($tgl_from,$tgl_to,$periode)
    {
      $data = $this->db->query("
        INSERT INTO tb_d_tampung_gaji (
                      id_karyawan,
                      periode,
                      kode_akun,
                      nama_akun,
                      jenis_akun,
                      keterangan,
                      nominal,
                      user_ins,
                      kode_kantor
                    )
        SELECT A.id_karyawan,'".$periode."' AS periode,'LIBUR' AS kode_akun,'Insentif Hari Libur' AS nama_akun,'DEBIT' AS jenis_akun,
               '' AS ket,SUM(nominal) AS nominal ,'' AS user_ins,'' AS kode_kantor
        FROM (
          SELECT A.id_karyawan,tanggal,B.nominal
          FROM tb_jam_kerja_karyawan A
          LEFT JOIN tb_tunjangan_karyawan B
            ON A.id_karyawan = B.id_karyawan 
          LEFT JOIN tb_tunjangan C
            ON B.id_tunjangan = C.id_tunjangan
          WHERE nama_jam NOT IN('LIBUR','CUTI','Izin','Sakit SKD','Sakit Tanpa SKD','Absen','Libur Hari Raya','Libur HR Potong','Dirumahkan')
          AND tanggal BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND C.kode_tunjangan = 'LIBUR'
        ) A LEFT JOIN
        (
          SELECT db_date
          FROM tb_calendar
          WHERE db_date BETWEEN '".$tgl_from."' AND '".$tgl_to."'
          AND holiday_flag = '1' AND day_name <> 'Sunday'
        ) B ON A.tanggal = B.db_date
        WHERE db_date IS NOT NULL
        GROUP BY A.id_karyawan
      ");
    }

    function hitung_potongan_absen($id_karyawan,$kode_kantor,$tgl_from,$tgl_to,$periode)
    {
      $data = $this->db->query("
          INSERT INTO tb_d_tampung_gaji (
            id_karyawan,
            periode,
            kode_akun,
            nama_akun,
            jenis_akun,
            keterangan,
            nominal,
            user_ins,
            kode_kantor
          )
          SELECT id_karyawan,'".$periode."' AS periode,'ABSEN' AS kode,'Absensi' nama_akun,'KREDIT' AS jenis,CONCAT(total_hari - total_kerja,' hari') AS keterangan, (total_hari - total_kerja) * besar_potong    AS potongan_absen,
          '".$this->session->userdata('ses_id_karyawan')."' AS user_ins,kode_kantor
          FROM (
            SELECT A.id_karyawan,SUM(COALESCE(B.ctx_kerja,0)) total_kerja,SUM(COALESCE(A.ctx_hari,0)) AS total_hari,A.kode_kantor,besar_potong
            FROM
            (
               SELECT id_karyawan,tanggal,kode_kantor,'1' AS ctx_hari
               FROM tb_jam_kerja_karyawan
               WHERE id_karyawan = '".$id_karyawan."' AND kode_kantor = '".$kode_kantor."'
               AND nama_jam NOT IN('LIBUR','CUTI','Izin','Sakit SKD','DINAS LUAR','Sakit Tanpa SKD','Absen','Libur Hari Raya','Libur HR Potong','Dirumahkan') AND
               tanggal BETWEEN '".$tgl_from."' AND '".$tgl_to."'
            ) A LEFT JOIN
            (
              SELECT DISTINCT id_karyawan,kode_kantor,tanggal,'1' AS ctx_kerja
              FROM (
                SELECT C.id_karyawan,DATE_FORMAT(tanggal,'%Y-%m-%d') tanggal,C.kode_kantor
                FROM tb_log_absen A
                LEFT JOIN tb_att_employ B
                  ON A.id_cabang = B.id_cabang AND A.id_karyawan = B.id_empl
                LEFT JOIN tb_karyawan C
                  ON B.id_empl = C.id_mesin_empl AND B.kode_kantor = C.kode_kantor
                WHERE C.id_karyawan = '".$id_karyawan."' AND C.kode_kantor = '".$kode_kantor."'
                AND DATE_FORMAT(tanggal,'%Y-%m-%d') BETWEEN '".$tgl_from."' AND '".$tgl_to."'
              ) AA
            ) B ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor AND A.tanggal = B.tanggal
            LEFT JOIN (
                select id_karyawan,kode_kantor,besar_gaji/26 as besar_potong
                from tb_gaji_pokok
                where id_karyawan = '".$id_karyawan."' 
            ) C 
              ON A.id_karyawan = C.id_karyawan AND A.kode_kantor = C.kode_kantor
            WHERE DATE_FORMAT(A.tanggal,'%Y-%m-%d') BETWEEN '".$tgl_from."' AND '".$tgl_to."'
            GROUP BY A.id_karyawan
          ) A
        ");
    }

    function hitung_potongan_absen_v2($tgl_from,$tgl_to,$periode)
    {
       $data = $this->db->query("
            INSERT INTO tb_d_tampung_gaji (
              id_karyawan,
              periode,
              kode_akun,
              nama_akun,
              jenis_akun,
              keterangan,
              nominal,
              user_ins,
              kode_kantor
            )
            SELECT A.id_karyawan,'".$periode."' AS periode,'ABSEN' AS kode,'Absensi' nama_akun,'KREDIT' AS jenis,'' AS keterangan, 
                   FLOOR(total_absen * besar_potong) AS potongan_absen,'".$this->session->userdata('ses_id_karyawan')."' AS user_ins,A.kode_kantor
            FROM
            (
              SELECT id_karyawan,COUNT(id_karyawan) AS total_absen,kode_kantor
              FROM tb_jam_kerja_karyawan
              WHERE nama_jam IN('Absen','Dirumahkan','Libur HR Potong','Sakit Tanpa SKD','Lembur OFF','Izin')
              AND tanggal BETWEEN '".$tgl_from."' AND '".$tgl_to."'
              GROUP BY id_karyawan,kode_kantor
            ) A
            LEFT JOIN (
              SELECT id_karyawan,kode_kantor,besar_gaji/26 AS besar_potong
              FROM tb_gaji_pokok
            ) B ON A.id_karyawan = B.id_karyawan
        ");
    }

    function detail_potongan_absen($id_karyawan,$tgl_from,$tgl_to)
    {
       $data = $this->db->query("
          SELECT tanggal,nama_jam,besar_potong
          FROM
          (
            SELECT id_karyawan ,tanggal,nama_jam
            FROM tb_jam_kerja_karyawan
            WHERE nama_jam IN('Absen','Dirumahkan','Libur HR Potong','Sakit Tanpa SKD','Lembur OFF','Izin')
            AND tanggal BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND id_karyawan = '".$id_karyawan."'
          ) A
          LEFT JOIN (
            SELECT id_karyawan,kode_kantor,besar_gaji/27 AS besar_potong
            FROM tb_gaji_pokok
            WHERE id_karyawan = '".$id_karyawan."'
          ) B ON A.id_karyawan = B.id_karyawan
       ")->result(); 

       return $data;
    }

    function hitung_kenaikan_setahun($tgl_from,$tgl_to,$periode)
    {
       $data = $this->db->query("
            INSERT INTO tb_d_tampung_gaji (
              id_karyawan,
              periode,
              kode_akun,
              nama_akun,
              jenis_akun,
              keterangan,
              nominal,
              user_ins,
              kode_kantor
            )
            SELECT id_karyawan,'".$periode."' AS periode,'KENAIKAN' AS kode,'Kenaikan Gaji' nama_akun,'DEBIT' AS jenis,CONCAT((ROUND(masa_kerja / 12)),' thn ', tgl_diterima) AS keterangan, 100000 AS nominal,'".$this->session->userdata('ses_id_karyawan')."' AS user_ins,A.kode_kantor
            FROM
            (
              SELECT id_karyawan,tgl_diterima,TIMESTAMPDIFF(MONTH,tgl_diterima,NOW()) AS masa_kerja,kode_kantor
              FROM tb_karyawan A
              WHERE isDefault = '1' AND A.isAktif = 'DITERIMA'
              AND DATE_FORMAT(tgl_diterima,'%m-%d') BETWEEN DATE_FORMAT('".$tgl_from."','%m-%d') AND DATE_FORMAT('".$tgl_to."','%m-%d')
              AND TIMESTAMPDIFF(MONTH,tgl_diterima,NOW()) > 10
            ) A WHERE ROUND(masa_kerja / 12) IN('1','3','5','7','9','11')
        ");
    }

    function hitung_ins_resp_apoteker($tgl_from,$tgl_to,$periode)
    {
       $data = $this->db->query("
          INSERT INTO tb_d_tampung_gaji (
                id_karyawan,
                periode,
                kode_akun,
                nama_akun,
                jenis_akun,
                keterangan,
                nominal,
                user_ins,
                kode_kantor
              )
          SELECT id_sales,'".$periode."' AS periode,'ASSIST' AS kode,'Ins Ass Dokter / Resep' nama_akun,'DEBIT' AS jenis,'' AS keterangan, SUM(fee_nominal) AS fee_nominal,
                 '".$this->session->userdata('ses_id_karyawan')."' AS user_ins,'' kode_kantor
          FROM
          (
            SELECT id_sales,CASE WHEN COALESCE(stat_pasien,'') = 'BARU' THEN
                 CASE WHEN jml_resep_apt >= 5 AND jml_resep_dokter >= 10 THEN (subtotal_apt + subtotal_dokter) * 0.01 
                WHEN jml_resep_apt BETWEEN 3 AND 4 AND jml_resep_dokter >= 10 THEN (subtotal_apt + subtotal_dokter) * 0.005
                WHEN jml_resep_apt >= 10 AND jml_resep_dokter IS NULL THEN subtotal_apt * 0.01
                 ELSE 0 END
                   WHEN COALESCE(stat_pasien,'') = 'LAMA' THEN
                 CASE WHEN jml_resep_apt >= 3 AND jml_resep_dokter >= 5 THEN (subtotal_apt + subtotal_dokter) * 0.01 
                WHEN jml_resep_apt = 2 AND jml_resep_dokter >= 5 THEN (subtotal_apt + subtotal_dokter) * 0.005 
                WHEN jml_resep_apt >= 5 AND jml_resep_dokter IS NULL THEN (subtotal_apt) * 0.01
                 ELSE 0 END
                   END AS fee_nominal
            FROM
            (
              SELECT  id_sales,'1' AS divi,COUNT(B.id_produk) AS jml_resep_apt,tgl_h_penjualan,no_costmer,SUM(harga * jumlah) AS subtotal_apt,
                CASE WHEN tgl_h_penjualan = tgl_registrasi THEN 'BARU' ELSE 'LAMA' END AS stat_pasien
              FROM tb_h_penjualan A
              LEFT JOIN tb_d_penjualan B
                ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
              LEFT JOIN tb_produk C
                ON B.id_produk = C.id_produk AND B.kode_kantor = C.kode_kantor
              LEFT JOIN tb_costumer D
                ON A.id_costumer = D.id_costumer AND A.kode_kantor = D.kode_kantor
              WHERE tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
              AND id_sales <> '' AND id_dokter = ''
              AND nama_produk NOT IN('PAPER BAG BESAR','PAPER BAG SEDANG',
              'PAPER BAG KECIL','SPON','MATERAI','ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA',
              'ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','SPREI+SPONS')
              GROUP BY tgl_h_penjualan,no_costmer
            ) A LEFT JOIN (
              SELECT '1' AS divi,COUNT(B.id_produk) AS jml_resep_dokter,tgl_h_penjualan,no_costmer,SUM(harga * jumlah) AS subtotal_dokter
              FROM tb_h_penjualan A
              LEFT JOIN tb_d_penjualan B
                ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
              LEFT JOIN tb_produk C
                ON B.id_produk = C.id_produk AND B.kode_kantor = C.kode_kantor
              WHERE id_dokter <> '' AND id_sales = '' AND tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
              AND nama_produk NOT IN('PAPER BAG BESAR','PAPER BAG SEDANG',
              'PAPER BAG KECIL','SPON','MATERAI','ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA',
              'ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','SPREI+SPONS') AND C.isProduk = 'PRODUK'
              GROUP BY tgl_h_penjualan,no_costmer
            ) B ON A.divi = B.divi AND A.tgl_h_penjualan = B.tgl_h_penjualan AND A.no_costmer = B.no_costmer
          ) A GROUP BY id_sales
      ");
    }


    function detail_ins_ass($id_karyawan,$tgl_from,$tgl_to)
    {
       $data = $this->db->query("
          SELECT no_faktur,tgl_h_penjualan,nama_costumer,nama_produk,nama_dok,nama_ass,2000 AS fee
          FROM tb_h_penjualan A
          LEFT JOIN tb_d_penjualan B
           ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
          LEFT JOIN tb_produk E
           ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
          LEFT JOIN tb_karyawan K
           ON B.id_ass = K.id_karyawan AND B.kode_kantor = K.kode_kantor
          LEFT JOIN tb_karyawan_jabatan J
           ON K.id_karyawan = J.id_karyawan AND K.kode_kantor = J.kode_kantor
          LEFT JOIN tb_jabatan T
           ON J.id_jabatan = T.id_jabatan AND J.kode_kantor = T.kode_kantor
          WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
          AND id_ass <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') 
          AND id_dok <> '' AND jenis_tindakan = 'DOKTER'
          AND (LOCATE('INFUS',nama_produk) = 0 OR LOCATE('INJECT',nama_produk) = 0 AND LOCATE('INJECT ACNE',nama_produk) > 0 )
          AND E.isProduk IN('JASA') AND id_ass = '".$id_karyawan."'
          AND E.nama_produk NOT IN('ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','ONGKIR')
        ")->result();

       return $data;
    }

    function detail_ins_apoteker($id_karyawan,$tgl_from,$tgl_to)
    {
       $data = $this->db->query("
          SELECT A.tgl_h_penjualan,A.nama_costumer,stat_pasien,jml_resep_dokter,subtotal_dokter,jml_resep_apt,subtotal_apt,CASE WHEN COALESCE(stat_pasien,'') = 'BARU' THEN
          CASE WHEN jml_resep_apt >= 5 AND jml_resep_dokter >= 10 THEN (subtotal_apt + subtotal_dokter) * 0.01 
          WHEN jml_resep_apt BETWEEN 3 AND 4 AND jml_resep_dokter >= 10 THEN (subtotal_apt + subtotal_dokter) * 0.005
          WHEN jml_resep_apt >= 10 AND jml_resep_dokter IS NULL THEN subtotal_apt * 0.01
          ELSE 0 END
          WHEN COALESCE(stat_pasien,'') = 'LAMA' THEN
          CASE WHEN jml_resep_apt >= 3 AND jml_resep_dokter >= 5 THEN (subtotal_apt + subtotal_dokter) * 0.01 
          WHEN jml_resep_apt = 2 AND jml_resep_dokter >= 5 THEN (subtotal_apt + subtotal_dokter) * 0.005 
          WHEN jml_resep_apt >= 5 AND jml_resep_dokter IS NULL THEN (subtotal_apt) * 0.01
          ELSE 0 END
          END AS fee_nominal
        FROM
        (
          SELECT  id_sales,'1' AS divi,COUNT(B.id_produk) AS jml_resep_apt,tgl_h_penjualan,nama_costumer,no_costmer,SUM(harga * jumlah) AS subtotal_apt,
          CASE WHEN tgl_h_penjualan = tgl_registrasi THEN 'BARU' ELSE 'LAMA' END AS stat_pasien
          FROM tb_h_penjualan A
          LEFT JOIN tb_d_penjualan B
          ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
          LEFT JOIN tb_produk C
          ON B.id_produk = C.id_produk AND B.kode_kantor = C.kode_kantor
          LEFT JOIN tb_costumer D
          ON A.id_costumer = D.id_costumer AND A.kode_kantor = D.kode_kantor
          WHERE tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
          AND id_sales <> '' AND id_dokter = '' AND id_sales = '".$id_karyawan."'
          AND nama_produk NOT IN('PAPER BAG BESAR','PAPER BAG SEDANG',
          'PAPER BAG KECIL','SPON','MATERAI','ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA',
          'ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','SPREI+SPONS')
          GROUP BY tgl_h_penjualan,no_costmer
        ) A LEFT JOIN (
          SELECT '1' AS divi,COUNT(B.id_produk) AS jml_resep_dokter,tgl_h_penjualan,no_costmer,SUM(harga * jumlah) AS subtotal_dokter
          FROM tb_h_penjualan A
          LEFT JOIN tb_d_penjualan B
          ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
          LEFT JOIN tb_produk C
          ON B.id_produk = C.id_produk AND B.kode_kantor = C.kode_kantor
          WHERE id_dokter <> '' AND id_sales = '' AND tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
          AND nama_produk NOT IN('PAPER BAG BESAR','PAPER BAG SEDANG',
          'PAPER BAG KECIL','SPON','MATERAI','ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA',
          'ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','SPREI+SPONS') AND C.isProduk = 'PRODUK'
          GROUP BY tgl_h_penjualan,no_costmer
        ) B ON A.divi = B.divi AND A.tgl_h_penjualan = B.tgl_h_penjualan AND A.no_costmer = B.no_costmer
        ORDER BY tgl_h_penjualan,stat_pasien
       ")->result();

       return $data;
    }

    function hitung_konsul_pasien_baru($id_karyawan,$tgl_from,$tgl_to,$periode)
    {
      $this->db->query("
          INSERT INTO tb_d_tampung_gaji (
            id_karyawan,
            periode,
            kode_akun,
            nama_akun,
            jenis_akun,
            keterangan,
            nominal,
            user_ins,
            kode_kantor
          )
          SELECT id_dokter,'".$periode."' periode,'KONSUL1' kode_akun,'Pasien Baru' nama_akun,'DEBIT' jenis_akun,'' keterangan,SUM(INS) nominal,'".$this->session->userdata('ses_id_karyawan')."' user_ins,'' kode_kantor
          FROM (
              SELECT id_dokter,nama_karyawan,tgl_diterima,id_h_penjualan,id_costumer,tgl_h_penjualan,nama_jasa,nama_produk,total_produk,
            CASE WHEN LOCATE('DETOX',nama_jasa) > 0 AND (LOCATE('PEEL',nama_jasa) > 0 OR LOCATE('MICRODERMABRASI',nama_jasa) > 0) AND total_produk >= 10 AND masa_kerja >= 365 THEN 10000 
                 WHEN LOCATE('DETOX',nama_jasa) > 0 AND (LOCATE('PEEL',nama_jasa) > 0 OR LOCATE('MICRODERMABRASI',nama_jasa) > 0) AND total_produk >= 10 AND masa_kerja < 365 THEN 5000
            ELSE
                 CASE WHEN LOCATE('DETOX',nama_jasa) > 0 AND (LOCATE('PEEL',nama_jasa) > 0 OR LOCATE('MICRODERMABRASI',nama_jasa) > 0) AND total_produk >= 5 AND total_produk < 10 AND masa_kerja >= 365 THEN 5000 
                WHEN LOCATE('DETOX',nama_jasa) > 0 AND (LOCATE('PEEL',nama_jasa) > 0 OR LOCATE('MICRODERMABRASI',nama_jasa) > 0) AND total_produk >= 5 AND total_produk < 10 AND masa_kerja < 365 THEN 2500 
                 ELSE 0 END
            END AS INS
            FROM 
            (
                SELECT  id_dokter,nama_karyawan,tgl_diterima,id_h_penjualan,id_costumer,tgl_h_penjualan,
                        GROUP_CONCAT(nama_jasa SEPARATOR ', ') nama_jasa,
                        GROUP_CONCAT(nama_produk SEPARATOR ', ') nama_produk,
                        SUM(total_jasa) total_jasa,SUM(total_produk) total_produk,masa_kerja
                FROM
                (
            SELECT  id_dokter,nama_karyawan,tgl_diterima,id_h_penjualan,id_costumer,tgl_h_penjualan,
              CASE WHEN isProduk = 'JASA' THEN nama_produk ELSE '' END AS nama_jasa,
              CASE WHEN isProduk = 'PRODUK' THEN nama_produk ELSE '' END AS nama_produk,
              CASE WHEN isProduk = 'JASA' THEN jumlah ELSE '' END AS total_jasa,
              CASE WHEN isProduk = 'PRODUK' THEN jumlah ELSE '' END AS total_produk,masa_kerja
            FROM
            (
              SELECT A.id_dokter,D.nama_karyawan,tgl_diterima,A.id_h_penjualan,A.id_costumer,tgl_h_penjualan,GROUP_CONCAT(E.nama_produk SEPARATOR ', ') nama_produk,
               SUM(B.jumlah) AS jumlah,A.kode_kantor,E.isProduk,DATEDIFF(tgl_h_penjualan,tgl_diterima) AS masa_kerja
              FROM tb_h_penjualan A
              LEFT JOIN tb_d_penjualan B
                ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
              LEFT JOIN tb_costumer C 
                ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
              LEFT JOIN tb_karyawan D
                ON A.id_dokter = D.id_karyawan AND A.kode_kantor = D.kode_kantor
              LEFT JOIN tb_produk E
                ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
              WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
              AND id_dokter <> '' AND type_h_penjualan = 'KONSULTASI KECANTIKAN'
              AND tgl_h_penjualan = tgl_registrasi AND sts_penjualan IN ('SELESAI','PEMBAYARAN') 
              AND A.id_dokter = '".$id_karyawan."' AND E.isProduk IN('JASA','PRODUK') 
              AND E.nama_produk NOT IN('PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','SPON','MATERAI','ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','SPREI+SPONS')
              GROUP BY A.id_dokter,D.nama_karyawan,tgl_diterima,A.id_h_penjualan,A.id_costumer,tgl_h_penjualan,E.isProduk
            ) A 
              ) A GROUP BY id_dokter,nama_karyawan,tgl_diterima,id_h_penjualan,id_costumer,tgl_h_penjualan,masa_kerja
            ) A
          ) A

      ");
    }

    function hitung_konsul_pasien_lama($id_karyawan,$tgl_from,$tgl_to,$periode)
    {
      $this->db->query("
          INSERT INTO tb_d_tampung_gaji (
            id_karyawan,
            periode,
            kode_akun,
            nama_akun,
            jenis_akun,
            keterangan,
            nominal,
            user_ins,
            kode_kantor
          )
          SELECT id_dokter,'".$periode."' periode,'KONSUL2' kode_akun,'Pasien Lama' nama_akun,'DEBIT' jenis_akun,'' keterangan,SUM(INS) nominal,'".$this->session->userdata('ses_id_karyawan')."' user_ins,'' kode_kantor
          FROM (
              SELECT id_dokter,nama_karyawan,tgl_diterima,id_h_penjualan,id_costumer,tgl_h_penjualan,nama_jasa,nama_produk,total_produk,
            CASE WHEN (LOCATE('DOKTER',jenis_tindakan) > 0 OR LOCATE('GABUNGAN',jenis_tindakan) > 0) AND total_produk >= 5 AND masa_kerja >= 365 THEN 5000
                 WHEN (LOCATE('DOKTER',jenis_tindakan) > 0 OR LOCATE('GABUNGAN',jenis_tindakan) > 0) AND total_produk >= 5 AND masa_kerja < 365 THEN 2000
                 WHEN (LOCATE('TERAPIS',jenis_tindakan) > 0 OR LOCATE('GABUNGAN',jenis_tindakan) > 0) AND total_produk >= 10 AND masa_kerja >= 365 THEN 5000
                 WHEN (LOCATE('TERAPIS',jenis_tindakan) > 0 OR LOCATE('GABUNGAN',jenis_tindakan) > 0) AND total_produk >= 10 AND masa_kerja < 365 THEN 2000
            ELSE 
                 CASE WHEN (LOCATE('DOKTER',jenis_tindakan) > 0 OR LOCATE('GABUNGAN',jenis_tindakan) > 0) AND total_produk < 5 AND masa_kerja >= 365 THEN 2500
                WHEN (LOCATE('DOKTER',jenis_tindakan) > 0 OR LOCATE('GABUNGAN',jenis_tindakan) > 0) AND total_produk < 5 AND masa_kerja < 365 THEN 1000
                WHEN (LOCATE('TERAPIS',jenis_tindakan) > 0 OR LOCATE('GABUNGAN',jenis_tindakan) > 0) AND total_produk < 10 AND masa_kerja >= 365 THEN 2500
                WHEN (LOCATE('TERAPIS',jenis_tindakan) > 0 OR LOCATE('GABUNGAN',jenis_tindakan) > 0) AND total_produk < 10 AND masa_kerja < 365 THEN 1000
                 ELSE 0 END
            END AS INS,jenis_tindakan
               FROM 
              (
                SELECT  id_dokter,nama_karyawan,tgl_diterima,id_h_penjualan,id_costumer,tgl_h_penjualan,
            GROUP_CONCAT(nama_jasa SEPARATOR ', ') nama_jasa,
            GROUP_CONCAT(nama_produk SEPARATOR ', ') nama_produk,
            GROUP_CONCAT(jenis_tindakan SEPARATOR ', ') jenis_tindakan,
            SUM(total_jasa) total_jasa,SUM(total_produk) total_produk,masa_kerja
                FROM
                (
            SELECT  id_dokter,nama_karyawan,tgl_diterima,id_h_penjualan,id_costumer,tgl_h_penjualan,
              CASE WHEN isProduk = 'JASA' THEN nama_produk ELSE '' END AS nama_jasa,
              CASE WHEN isProduk = 'PRODUK' THEN nama_produk ELSE '' END AS nama_produk,
              CASE WHEN isProduk = 'JASA' THEN jumlah ELSE '' END AS total_jasa,
              CASE WHEN isProduk = 'PRODUK' THEN jumlah ELSE '' END AS total_produk,masa_kerja,jenis_tindakan
            FROM
            (
              SELECT A.id_dokter,D.nama_karyawan,tgl_diterima,A.id_h_penjualan,A.id_costumer,tgl_h_penjualan,GROUP_CONCAT(E.nama_produk SEPARATOR ', ') nama_produk,
               SUM(B.jumlah) AS jumlah,A.kode_kantor,E.isProduk,DATEDIFF(tgl_h_penjualan,tgl_diterima) AS masa_kerja,COALESCE(jenis_tindakan,'') jenis_tindakan
              FROM tb_h_penjualan A
              LEFT JOIN tb_d_penjualan B
                ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
              LEFT JOIN tb_costumer C 
                ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
              LEFT JOIN tb_karyawan D
                ON A.id_dokter = D.id_karyawan AND A.kode_kantor = D.kode_kantor
              LEFT JOIN tb_produk E
                ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
              WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
              AND id_dokter <> '' AND type_h_penjualan = 'KONSULTASI KECANTIKAN'
              AND tgl_h_penjualan <> tgl_registrasi AND sts_penjualan IN ('SELESAI','PEMBAYARAN') 
              AND A.id_dokter = '".$id_karyawan."' AND E.isProduk IN('JASA','PRODUK')
              AND E.nama_produk NOT IN('PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','SPON','MATERAI','ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','SPREI+SPONS')
              GROUP BY A.id_dokter,D.nama_karyawan,tgl_diterima,A.id_h_penjualan,A.id_costumer,tgl_h_penjualan,E.isProduk,COALESCE(jenis_tindakan,'')
            ) A 
              ) A GROUP BY id_dokter,nama_karyawan,tgl_diterima,id_h_penjualan,id_costumer,tgl_h_penjualan,masa_kerja
            ) A WHERE nama_jasa <> ''
          ) A
      ");
    }

    function total_tunjangan_karyawan($id_karyawan,$kode_kantor,$periode)
    {
      $data = $this->db->query("
        SELECT SUM(nominal) AS total 
        FROM tb_d_payment
        WHERE id_karyawan = '".$id_karyawan."'
        AND periode = '".$periode."' AND jenis_akun = 'DEBIT'
      ")->row();

      if(!empty($data))
      {
        return $data->total;
      } else {
        return 0;
      }
    }

    function total_potongan_karyawan($id_karyawan,$kode_kantor,$periode)
    {
      $data = $this->db->query("
        SELECT SUM(nominal) AS total 
        FROM tb_d_payment
        WHERE id_karyawan = '".$id_karyawan."'
        AND periode = '".$periode."' AND jenis_akun = 'KREDIT'
      ")->row();

      if(!empty($data))
      {
        return $data->total;
      } else {
        return 0;
      }
    }

    function insert_d_tampung_gaji($data)
    {
      $this->db->insert('tb_d_tampung_gaji',$data);
    }

    function delete_d_tampung($periode,$kode_akun)
    {
      $this->db->query("
          DELETE FROM tb_d_tampung_gaji
          WHERE periode = '".$periode."' AND kode_akun = '".$kode_akun."'
      ");
    }

    function reset_d_payment($periode)
    {
      $this->db->query("
          DELETE FROM tb_d_payment
          WHERE periode = '".$periode."' -- AND kode_akun NOT IN('LATE','ABSEN','KONSUL1','KONSUL2','LEMBUR','TINDAKAN','UPSELLING','UM','LIBUR','LEBIH10','LEBIH1','PUNISH','KENAIKAN','ASSIST')
      ");
    }

    function delete_d_tampung_by_id($id)
    {
      $this->db->query("
          DELETE FROM tb_d_tampung_gaji
          WHERE id_tampung = '".$id."'
      ");
    }

    function tarik_late_cabang($tgl_from,$tgl_to,$cabang,$id_karyawan)
    {
       $result = $this->db->query("
         CALL SP_REKAP_ABSEN('".$tgl_from."','".$tgl_to."',".$cabang.",'".$id_karyawan."');
       ");

      $res = $result->result();
      //mysqli_next_result( $this->db->conn_id );
      return $res;      
    }

    function tarik_lembur_v2($tgl_from,$tgl_to,$periode)
    {
      $this->db->query("
           INSERT INTO tb_d_tampung_gaji (
              id_karyawan,
              periode,
              kode_akun,
              nama_akun,
              jenis_akun,
              keterangan,
              nominal,
              user_ins,
              kode_kantor
            )
            SELECT A.id_karyawan,'".$periode."' AS periode,'LEMBUR' AS kode,'Insentif Lembur' nama_akun,'DEBIT' AS jenis,'' AS keterangan, 
                   SUM(nominal * kali_lembur) AS nominal,'".$this->session->userdata('ses_id_karyawan')."' AS user_ins,A.kode_kantor
            FROM
            (
              SELECT id_karyawan,kode_kantor,masa_kerja,
                     CASE WHEN nama_jabatan = 'DOKTER' AND masa_kerja < 1 THEN 20000
                          WHEN nama_jabatan = 'DOKTER' AND masa_kerja >= 1 THEN 40000
                          WHEN nama_jabatan = 'APOTEKER' THEN 20000
                          ELSE 
                  CASE WHEN masa_kerja < 2 THEN 10000 ELSE 20000 END
                END AS nominal,kali_lembur - mod_lembur AS kali_lembur
              FROM
              (
                SELECT A.id_karyawan,A.kode_kantor,FLOOR(DATEDIFF(NOW(),tgl_diterima) / 365) AS masa_kerja,ROUND(jam_lembur / 60,2) AS kali_lembur,
                       nama_jabatan,(ROUND(jam_lembur / 60,2)) % 0.5 AS mod_lembur
                FROM tb_jam_kerja_karyawan A
                LEFT JOIN tb_karyawan B
                  ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
                LEFT JOIN tb_karyawan_jabatan C
                  ON B.id_karyawan = C.id_karyawan AND B.kode_kantor = C.kode_kantor
                LEFT JOIN tb_jabatan D
                  ON C.id_jabatan = D.id_jabatan AND C.kode_kantor = D.kode_kantor
                WHERE nama_jam NOT IN('Absen','Dirumahkan','Libur HR Potong','Sakit Tanpa SKD')
                AND tanggal BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND jam_lembur > 29
                AND nama_jabatan NOT IN('PAYROLL','HRD','AKUNTING','SECURITY','Admin Aplikasi','SUPERVISOR','IT','OPERASIONAL')
              ) A
            ) A GROUP BY id_karyawan
      ");
    }

    function detail_ins_lembur($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
           SELECT tanggal,nama_karyawan,jam_lembur,
                 CASE WHEN nama_jabatan = 'DOKTER' AND masa_kerja < 1 THEN 20000
              WHEN nama_jabatan = 'DOKTER' AND masa_kerja >= 1 THEN 40000
              WHEN nama_jabatan = 'APOTEKER' THEN 20000
               ELSE 
              CASE WHEN masa_kerja < 2 THEN 10000 ELSE 20000 END
              END AS nominal,kali_lembur - mod_lembur AS kali_lembur
                FROM
                (
                  SELECT tanggal,A.id_karyawan,nama_karyawan,A.kode_kantor,FLOOR(DATEDIFF(NOW(),tgl_diterima) / 365) AS masa_kerja,ROUND(jam_lembur / 60,2) AS kali_lembur,
                         nama_jabatan,(ROUND(jam_lembur / 60,2)) % 0.5 AS mod_lembur,jam_lembur 
                  FROM tb_jam_kerja_karyawan A
                  LEFT JOIN tb_karyawan B
                    ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
                  LEFT JOIN tb_karyawan_jabatan C
                    ON B.id_karyawan = C.id_karyawan AND B.kode_kantor = C.kode_kantor
                  LEFT JOIN tb_jabatan D
                    ON C.id_jabatan = D.id_jabatan AND C.kode_kantor = D.kode_kantor
                  WHERE nama_jam NOT IN('Absen','Dirumahkan','Libur HR Potong','Sakit Tanpa SKD')
                  AND tanggal BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND jam_lembur > 29
                  AND nama_jabatan NOT IN('PAYROLL','HRD','AKUNTING','SECURITY','Admin Aplikasi','SUPERVISOR','IT','OPERASIONAL')
                  AND A.id_karyawan = '".$id_karyawan."'
                ) A
      ")->result();

       return $data;

    }

    function tarik_lembur($tgl_from,$tgl_to,$cabang,$id_karyawan)
    {
       $result = $this->db->query("
         CALL SP_REKAP_ABSEN('".$tgl_from."','".$tgl_to."',".$cabang.",'".$id_karyawan."');
       ");

      $res = $result->result();
      //mysqli_next_result( $this->db->conn_id );
      return $res;      
    }

    function list_jam_kerja($id_karyawan,$tgl_from,$tgl_to,$kode_kantor)
    {
      $data = $this->db->query("
          SELECT A.id_karyawan,A.tanggal,jam_masuk,jam_keluar,A.kode_kantor,B.id_mesin_empl
          FROM tb_jam_kerja_karyawan A
          LEFT JOIN tb_karyawan B
            ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
          WHERE A.id_karyawan = '".$id_karyawan."' AND A.tanggal BETWEEN '".$tgl_from."' AND '".$tgl_to."'
          AND nama_jam NOT IN('LIBUR','CUTI','Izin','Sakit SKD','Sakit Tanpa SKD','Absen','Libur Hari Raya','Libur HR Potong','Dirumahkan','Lembur OFF')
      ")->result();

      return $data;
    }

    function detail_penggajian($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
          SELECT id_karyawan,nama_produk,harga_konversi,jumlah_konversi,COALESCE(nama_diskon,'') AS nama_diskon,
                  CASE WHEN LOCATE('INFUS',nama_produk) > 0 OR LOCATE('THREADLIFT',nama_produk) > 0 OR LOCATE('BOTOX',nama_produk) > 0 OR LOCATE('FILLER',nama_produk) > 0 
                       THEN 6 ELSE 5 END AS fee,
                  CASE WHEN LOCATE('INFUS',nama_produk) > 0 OR LOCATE('THREADLIFT',nama_produk) > 0 OR LOCATE('BOTOX',nama_produk) > 0 OR LOCATE('FILLER',nama_produk) > 0 
                      THEN (jumlah_konversi * harga_konversi) * 0.06 ELSE (jumlah_konversi * harga_konversi) * 0.05 END AS fee_tindakan
          FROM
          (
            SELECT id_karyawan,CASE WHEN nama_produk IN('GLAFIDSYA ACNE GLOW PEEL','GLAFIDSYA SUPER ACNE PEEL','GLAFIDSYA ANTI FLEX PEEL','GLAFIDSYA GLOWING PEEL','GLAFIDSYA PORI PEEL') THEN 'GLAFIDSYA PEEL'
                  WHEN nama_produk IN('FACIAL PORI PEEL','FACIAL GLOWING PEEL','FACIAL FLEX PEEL','FACIAL ANTI AGING PEEL','FACIAL ACNE PEEL') THEN 'FACIAL PEEL' 
                  ELSE nama_produk END AS nama_produk,
                   SUM(CASE WHEN nama_produk LIKE '%GLAFIDSYA%' THEN jumlah_konversi ELSE jumlah_konversi END) AS jumlah_konversi,nama_diskon,
                   harga_konversi
            FROM
            (
              SELECT id_karyawan,nama_produk,
                CASE WHEN LOCATE('BUY 1 GET 1',nama_produk) > 0 OR diskon = 100 THEN 
                  harga_konversi
                     ELSE 
                  ROUND((harga_konversi - diskon_konversi),-3)
                END AS harga_konversi,jumlah_konversi,nama_diskon
              FROM (
                SELECT B.id_dok AS id_karyawan,A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,
                   A.kode_kantor,E.isProduk,B.id_h_diskon,nama_diskon,
                   CASE WHEN B.status_konversi = '*' THEN
                      B.jumlah * B.konversi
                    ELSE
                      B.jumlah / B.konversi
                    END AS jumlah_konversi
                    ,CASE WHEN nama_produk IN('FACIAL ACNE PEEL','FACIAL FLEX PEEL','FACIAL GLOWING PEEL') THEN 700000 
                    WHEN nama_produk LIKE '%FACIAL SUPER REJUVE%' THEN 401000
                     ELSE 
                    CASE WHEN B.status_konversi = '*' THEN (B.harga / 1.1) / B.konversi ELSE B.harga % B.konversi END
                    END AS harga_konversi
                    ,CASE WHEN B.optr_diskon = '%' THEN
                      ((B.harga / 1.1)/100) * B.diskon ELSE B.diskon
                    END AS diskon_konversi,B.optr_diskon,B.diskon 
                FROM tb_h_penjualan A
                LEFT JOIN tb_d_penjualan B
                   ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
                LEFT JOIN tb_costumer C 
                   ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
                LEFT JOIN tb_produk E
                   ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
                LEFT JOIN tb_h_diskon F
                   ON A.id_h_diskon = F.id_h_diskon AND B.kode_kantor = F.kode_kantor
                WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
                AND B.id_dok <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
                AND E.isProduk IN('JASA') AND B.id_dok = '".$id_karyawan."'
                AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD')
              ) A 
            ) A GROUP BY id_karyawan,CASE WHEN nama_produk IN('GLAFIDSYA ACNE GLOW PEEL','GLAFIDSYA SUPER ACNE PEEL','GLAFIDSYA ANTI FLEX PEEL','GLAFIDSYA GLOWING PEEL','GLAFIDSYA PORI PEEL') THEN 'GLAFIDSYA PEEL'
                  WHEN nama_produk IN('FACIAL PORI PEEL','FACIAL GLOWING PEEL','FACIAL FLEX PEEL','FACIAL ANTI AGING PEEL','FACIAL ACNE PEEL') THEN 'FACIAL PEEL' 
                  ELSE nama_produk END,harga_konversi,nama_diskon
          ) A
          ORDER BY nama_produk,nama_diskon
      ")->result();
      return $data;
    }

    function detail_dokter_mentah($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
          SELECT id_dokter,nama_karyawan,no_faktur,nama_costumer,tgl_h_penjualan,nama_produk,nama_terapis,
               kode_kantor,isProduk,masa_kerja,id_h_diskon,COALESCE(nama_diskon,'') nama_diskon,jumlah_konversi,harga_konversi,optr_diskon,diskon,
               subtotal,
               CASE WHEN masa_kerja < 2 THEN 
              CASE WHEN LOCATE('INFUS',nama_produk) > 0 THEN 5 ELSE 3 END
               ELSE 
              CASE WHEN LOCATE('INFUS',nama_produk) > 0 OR LOCATE('THREADLIFT',nama_produk) > 0 OR LOCATE('BOTOX',nama_produk) > 0 OR LOCATE('FILLER',nama_produk) > 0 
             THEN 6 ELSE 5 END
          END AS fee_tindakan,
               CASE WHEN masa_kerja < 2 THEN 
              CASE WHEN LOCATE('INFUS',nama_produk) > 0 THEN (5 * subtotal) / 100 ELSE (3 * subtotal) / 100 END
               ELSE 
              CASE WHEN LOCATE('INFUS',nama_produk) > 0 OR LOCATE('THREADLIFT',nama_produk) > 0 OR LOCATE('BOTOX',nama_produk) > 0 OR LOCATE('FILLER',nama_produk) > 0 
            THEN (6 * subtotal) / 100 ELSE (5 * subtotal) / 100 END
          END AS fee_nominal
        FROM (
          SELECT id_dokter,nama_karyawan,no_faktur,nama_costumer,tgl_h_penjualan,nama_produk,nama_terapis,
           kode_kantor,isProduk,masa_kerja,id_h_diskon,nama_diskon,jumlah_konversi,harga_konversi,optr_diskon,diskon,
           CASE WHEN LOCATE('BUY 1 GET 1',nama_diskon) AND diskon = 100 THEN (jumlah_konversi * harga_konversi) / 2
          ELSE jumlah_konversi * (harga_konversi - diskon_konversi) END AS subtotal
          FROM (  
            SELECT B.id_dok AS id_dokter,D.nama_karyawan,T.nama_karyawan AS nama_terapis,A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,
           A.kode_kantor,E.isProduk,DATEDIFF(tgl_h_penjualan,D.tgl_diterima)/365 AS masa_kerja,B.id_h_diskon,nama_diskon,
           CASE WHEN B.status_konversi = '*' THEN
              B.jumlah * B.konversi
            ELSE
              B.jumlah / B.konversi
            END AS jumlah_konversi
            
            ,CASE WHEN B.status_konversi = '*' THEN
              (B.harga / 1.1) / B.konversi
            ELSE
              B.harga % B.konversi
            END AS harga_konversi
            
            ,CASE WHEN B.optr_diskon = '%' THEN
              ((B.harga / 1.1)/100) * B.diskon
            ELSE
              B.diskon
            END AS diskon_konversi,B.optr_diskon,B.diskon
            FROM tb_h_penjualan A
            LEFT JOIN tb_d_penjualan B
              ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
            LEFT JOIN tb_costumer C 
              ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
            LEFT JOIN tb_karyawan D
              ON B.id_dok = D.id_karyawan AND A.kode_kantor = D.kode_kantor
            LEFT JOIN tb_karyawan T
              ON B.id_ass = T.id_karyawan AND B.kode_kantor = T.kode_kantor
            LEFT JOIN tb_produk E
              ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
            LEFT JOIN tb_h_diskon F
              ON F.id_h_diskon = CASE WHEN B.id_d_diskon2 = '' THEN B.id_h_diskon
                                      WHEN B.id_h_diskon = '' THEN A.id_h_diskon ELSE '' END 
                 AND B.kode_kantor = F.kode_kantor
            WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
              AND B.id_dok <> '' AND type_h_penjualan IN('KONSULTASI KECANTIKAN','PENJUALAN LANGSUNG')
              AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN('DOKTER','GABUNGAN')
              AND (B.id_dok = '".$id_karyawan."' OR B.id_ass  AND E.isProduk IN('JASA','PRODUK') 
              AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD')
          ) A
         ) A ORDER BY tgl_h_penjualan,nama_costumer
        ")->result();
      return $data;
    }


    function detail_dokter_mentah_tahap1_lama($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
          SELECT tgl_h_penjualan,no_faktur,nama_costumer,nama_diskon,nama_produk,harga,isProduk,
            CASE WHEN group_produk = 'JASA,PRODUK' THEN '1%'
                        WHEN group_produk = 'JASA' THEN '0.75%'
                        WHEN group_produk = 'PRODUK' THEN '0.25%' ELSE 0 END AS fee,
                      SUM(CASE WHEN group_produk = 'JASA,PRODUK' THEN harga * 0.01
                      WHEN group_produk = 'JASA' THEN harga * 0.0075
                      WHEN group_produk = 'PRODUK' THEN harga * 0.0025 ELSE 0 END) AS fee_nominal
          FROM
          (
            SELECT tgl_h_penjualan,A.no_faktur,nama_costumer,nama_diskon,nama_produk,ROUND(jumlah_konversi * (harga_konversi - diskon_konversi)) AS harga,
              CTX2,id_dok_total,dokter_konsul,group_produk,isProduk
            FROM (
              SELECT A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,nama_diskon,
               A.kode_kantor,E.isProduk,A.id_dokter AS dokter_konsul,
               CASE WHEN B.status_konversi = '*' THEN
                  B.jumlah * B.konversi
                ELSE
                  B.jumlah / B.konversi
                END AS jumlah_konversi
                ,CASE WHEN B.status_konversi = '*' THEN (B.harga / 1.1) / B.konversi ELSE B.harga % B.konversi END AS harga_konversi
                ,CASE WHEN B.optr_diskon = '%' THEN ((B.harga / 1.1)/100) * B.diskon ELSE B.diskon
                END AS diskon_konversi,B.optr_diskon,B.diskon,B.id_dok,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX
              FROM tb_h_penjualan A
              LEFT JOIN tb_d_penjualan B
               ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
              LEFT JOIN tb_produk E
               ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
              LEFT JOIN tb_h_diskon F
                ON F.id_h_diskon = CASE WHEN B.id_d_diskon2 = '' THEN B.id_h_diskon WHEN B.id_h_diskon = '' THEN A.id_h_diskon ELSE '' END 
                   AND B.kode_kantor = F.kode_kantor
              WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
              AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                  'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
            ) A CROSS JOIN (
              SELECT no_faktur,SUM(CTX) AS CTX2,id_dok_total,GROUP_CONCAT(DISTINCT(isProduk),'') AS group_produk
              FROM (
                SELECT A.no_faktur,CASE WHEN id_dok = '' THEN A.id_dokter ELSE id_dok END AS id_dok_total,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX,B.isProduk
                FROM tb_h_penjualan A
                LEFT JOIN tb_d_penjualan B
                   ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
                LEFT JOIN tb_produk E
                   ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
                WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
                AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                    'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
              ) AA GROUP BY no_faktur
            ) B ON A.no_faktur = B.no_faktur
            WHERE CTX2 <> 0 AND id_dok_total = '".$id_karyawan."'
          ) A GROUP BY tgl_h_penjualan,no_faktur,nama_costumer,nama_diskon,nama_produk,harga,isProduk
      ")->result();
      return $data;
    }

    function detail_dokter_mentah_tahap1_baru($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
          SELECT tgl_h_penjualan,no_faktur,nama_costumer,nama_diskon,nama_produk,harga,isProduk,
            CASE WHEN group_produk = 'JASA,PRODUK' THEN '1% /2'
              WHEN group_produk = 'JASA' THEN '0.75% /2'
              WHEN group_produk = 'PRODUK' THEN '0.25% /2 ' ELSE 0 END AS fee,
            SUM(CASE WHEN group_produk = 'JASA,PRODUK' THEN harga * 0.01
            WHEN group_produk = 'JASA' THEN harga * 0.0075
            WHEN group_produk = 'PRODUK' THEN harga * 0.0025 ELSE 0 END) /2 AS fee_nominal
          FROM
          (
            SELECT tgl_h_penjualan,A.no_faktur,nama_costumer,nama_diskon,nama_produk,ROUND(jumlah_konversi * (harga_konversi - diskon_konversi)) AS harga,
              CTX2,id_dok_total,dokter_konsul,group_produk,isProduk
            FROM (
              SELECT A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,nama_diskon,
               A.kode_kantor,E.isProduk,A.id_dokter AS dokter_konsul,
               CASE WHEN B.status_konversi = '*' THEN
                  B.jumlah * B.konversi
                ELSE
                  B.jumlah / B.konversi
                END AS jumlah_konversi
                ,CASE WHEN B.status_konversi = '*' THEN (B.harga / 1.1) / B.konversi ELSE B.harga % B.konversi END AS harga_konversi
                ,CASE WHEN B.optr_diskon = '%' THEN ((B.harga / 1.1)/100) * B.diskon ELSE B.diskon
                END AS diskon_konversi,B.optr_diskon,B.diskon,B.id_dok,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX
              FROM tb_h_penjualan A
              LEFT JOIN tb_d_penjualan B
               ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
              LEFT JOIN tb_produk E
               ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
              LEFT JOIN tb_h_diskon F
                ON F.id_h_diskon = CASE WHEN B.id_d_diskon2 = '' THEN B.id_h_diskon WHEN B.id_h_diskon = '' THEN A.id_h_diskon ELSE '' END 
                   AND B.kode_kantor = F.kode_kantor
              WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
              AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                  'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
            ) A CROSS JOIN (
              SELECT no_faktur,SUM(CTX) AS CTX2,id_dok_total,GROUP_CONCAT(DISTINCT(isProduk),'') AS group_produk
              FROM (
                SELECT A.no_faktur,CASE WHEN id_dok = '' THEN A.id_dokter ELSE id_dok END AS id_dok_total,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX,B.isProduk
                FROM tb_h_penjualan A
                LEFT JOIN tb_d_penjualan B
                   ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
                LEFT JOIN tb_produk E
                   ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
                WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
                AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                    'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
              ) AA GROUP BY no_faktur
            ) B ON A.no_faktur = B.no_faktur
            WHERE CTX2 <> 0 AND id_dok_total = '".$id_karyawan."'
          ) A GROUP BY tgl_h_penjualan,no_faktur,nama_costumer,nama_diskon,nama_produk,harga,isProduk
      ")->result();
      return $data;
    }

    function detail_dokter_mentah_tahap2($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
          SELECT tgl_h_penjualan,no_faktur,nama_costumer,nama_diskon,nama_produk,harga,isProduk,
            CASE WHEN group_produk = 'JASA,PRODUK' THEN '2%'
              WHEN group_produk = 'JASA' THEN '1.50%'
              WHEN group_produk = 'PRODUK' THEN '0.50%' ELSE 0 END AS fee,
            SUM(CASE WHEN group_produk = 'JASA,PRODUK' THEN harga * 0.02
            WHEN group_produk = 'JASA' THEN harga * 0.015
            WHEN group_produk = 'PRODUK' THEN harga * 0.005 ELSE 0 END) AS fee_nominal
          FROM
          (
            SELECT tgl_h_penjualan,A.no_faktur,nama_costumer,nama_diskon,nama_produk,ROUND(jumlah_konversi * (harga_konversi - diskon_konversi)) AS harga,
              CTX2,id_dok_total,dokter_konsul,group_produk,isProduk
            FROM (
              SELECT A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,nama_diskon,
               A.kode_kantor,E.isProduk,A.id_dokter AS dokter_konsul,
               CASE WHEN B.status_konversi = '*' THEN
                  B.jumlah * B.konversi
                ELSE
                  B.jumlah / B.konversi
                END AS jumlah_konversi
                ,CASE WHEN B.status_konversi = '*' THEN (B.harga / 1.1) / B.konversi ELSE B.harga % B.konversi END AS harga_konversi
                ,CASE WHEN B.optr_diskon = '%' THEN ((B.harga / 1.1)/100) * B.diskon ELSE B.diskon
                END AS diskon_konversi,B.optr_diskon,B.diskon,B.id_dok,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX
              FROM tb_h_penjualan A
              LEFT JOIN tb_d_penjualan B
               ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
              LEFT JOIN tb_produk E
               ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
              LEFT JOIN tb_h_diskon F
                ON F.id_h_diskon = CASE WHEN B.id_d_diskon2 = '' THEN B.id_h_diskon WHEN B.id_h_diskon = '' THEN A.id_h_diskon ELSE '' END 
                   AND B.kode_kantor = F.kode_kantor
              WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
              AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                  'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
            ) A CROSS JOIN (
              SELECT no_faktur,SUM(CTX) AS CTX2,id_dok_total,GROUP_CONCAT(DISTINCT(isProduk),'') AS group_produk
              FROM (
                SELECT A.no_faktur,CASE WHEN id_dok = '' THEN A.id_dokter ELSE id_dok END AS id_dok_total,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX,B.isProduk
                FROM tb_h_penjualan A
                LEFT JOIN tb_d_penjualan B
                   ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
                LEFT JOIN tb_produk E
                   ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
                WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
                AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                    'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
              ) AA GROUP BY no_faktur
            ) B ON A.no_faktur = B.no_faktur
            WHERE CTX2 <> 0 AND id_dok_total = '".$id_karyawan."'
          ) A GROUP BY tgl_h_penjualan,no_faktur,nama_costumer,nama_diskon,nama_produk,harga,isProduk
      ")->result();
      return $data;
    }


    
    function summary_karyawan($id_karyawan,$kode_kantor,$periode,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
          SELECT A.id_karyawan,A.no_karyawan,A.nama_karyawan,besar_gaji,C.total_pendapatan,D.total_potongan,
                 besar_gaji + C.total_pendapatan - D.total_potongan - pph_21 AS total_bersih,
                  pph_21 as pajak,
                 izin,sakit,cuti,absen,izin+sakit+cuti+absen AS total_kehadiran
          FROM tb_karyawan A
          LEFT JOIN tb_payment P
            ON A.id_karyawan = P.id_karyawan AND P.periode = '".$periode."'
          LEFT JOIN tb_gaji_pokok B
            ON A.id_karyawan = B.id_karyawan
          LEFT JOIN (
            SELECT id_karyawan,SUM(nominal) AS total_pendapatan
            FROM tb_d_payment
            WHERE id_karyawan = '".$id_karyawan."'
            AND periode = '".$periode."' AND jenis_akun = 'DEBIT'
            GROUP BY id_karyawan
          ) C ON A.id_karyawan = C.id_karyawan
          LEFT JOIN (
            SELECT id_karyawan,SUM(nominal) AS total_potongan 
                  FROM tb_d_payment
                  WHERE id_karyawan = '".$id_karyawan."'
                  AND periode = '".$periode."' AND jenis_akun = 'KREDIT'
                  GROUP BY id_karyawan
          ) D ON A.id_karyawan = D.id_karyawan
          LEFT JOIN
          (
             SELECT id_karyawan,SUM(CASE WHEN nama_jam = 'Izin' THEN 1 ELSE 0 END) AS izin,
                    SUM(CASE WHEN nama_jam IN('Sakit Tanpa SKD','Sakit SKD') THEN 1 ELSE 0 END) AS sakit,
              SUM(CASE WHEN nama_jam IN('CUTI') THEN 1 ELSE 0 END) AS cuti,
              SUM(CASE WHEN nama_jam IN('Absen') THEN 1 ELSE 0 END) AS absen
             FROM tb_jam_kerja_karyawan
             WHERE id_karyawan = '".$id_karyawan."'
             AND tanggal BETWEEN '".$tgl_from."' AND '".$tgl_to."'
             GROUP BY id_karyawan
          ) E ON A.id_karyawan = E.id_karyawan
          WHERE A.id_karyawan = '".$id_karyawan."' AND A.kode_kantor = '".$kode_kantor."'
          AND isDefault = '1'
        ")->row();
      return $data;
    }

    function detail_uang_makan($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
          SELECT A.id_karyawan,A.tanggal,nama_jam,COALESCE(B.nominal,0) AS nominal,aktual_finger,jam_finger
          FROM
          (
            SELECT id_karyawan,tanggal,nama_jam
            FROM tb_jam_kerja_karyawan A
            WHERE nama_jam NOT IN('LIBUR','CUTI','Izin','Sakit SKD','Sakit Tanpa SKD','Absen','Libur Hari Raya','Libur HR Potong','Dirumahkan','Lembur OFF')
            AND tanggal BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND A.id_karyawan = '".$id_karyawan."'
          ) A LEFT JOIN(
            SELECT A.id_karyawan,A.nominal
            FROM tb_tunjangan_karyawan A
            LEFT JOIN tb_tunjangan B
             ON A.id_tunjangan = B.id_tunjangan
            WHERE kode_tunjangan = 'UM' AND A.id_karyawan = '".$id_karyawan."'
          ) B ON A.id_karyawan = B.id_karyawan
          LEFT JOIN
          (
              SELECT id_karyawan,kode_kantor,tanggal AS aktual_finger,GROUP_CONCAT(jam) jam_finger
              FROM (
                SELECT C.id_karyawan,DATE_FORMAT(tanggal,'%Y-%m-%d') tanggal,C.kode_kantor,
                 DATE_FORMAT(tanggal,'%H:%i') jam
                FROM tb_log_absen A
                LEFT JOIN tb_att_employ B
            ON A.id_cabang = B.id_cabang AND A.id_karyawan = B.id_empl
                LEFT JOIN tb_karyawan C
            ON B.id_empl = C.id_mesin_empl AND B.kode_kantor = C.kode_kantor
                WHERE C.id_karyawan = '".$id_karyawan."'
                AND DATE_FORMAT(tanggal,'%Y-%m-%d') BETWEEN '".$tgl_from."' AND '".$tgl_to."'
              ) A GROUP BY A.id_karyawan,kode_kantor,tanggal
          ) C ON A.id_karyawan = C.id_karyawan AND A.tanggal = C.aktual_finger
          ORDER BY tanggal
      ")->result();
      return $data;
    }

    function detail_tindakan_lebih($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
          SELECT *
            FROM (
              SELECT id_dokter,no_faktur,nama_karyawan,nama_costumer,tgl_h_penjualan,SUM(ROUND(subtotal)) AS subtotal,
                     150000 AS fee
              FROM ( 
                SELECT id_dokter,nama_karyawan,nama_costumer,no_faktur,tgl_h_penjualan,jumlah_konversi * (harga_konversi - diskon_konversi) AS subtotal
                FROM (
                   SELECT A.id_dokter,D.nama_karyawan,A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,
                   E.nama_produk,A.kode_kantor,E.isProduk,DATEDIFF(tgl_h_penjualan,D.tgl_diterima)/365 AS masa_kerja,
                   B.id_h_diskon,nama_diskon,
                   CASE WHEN B.status_konversi = '*' THEN
                      B.jumlah * B.konversi
                    ELSE
                      B.jumlah / B.konversi
                    END AS jumlah_konversi
                    ,CASE WHEN B.status_konversi = '*' THEN
                      B.harga / B.konversi
                    ELSE
                      B.harga % B.konversi
                    END AS harga_konversi
                    ,CASE WHEN B.optr_diskon = '%' THEN
                      (B.harga/100) * B.diskon
                    ELSE
                      B.diskon
                    END AS diskon_konversi,B.optr_diskon,B.diskon
                  FROM tb_h_penjualan A
                  LEFT JOIN tb_d_penjualan B
                    ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
                  LEFT JOIN tb_costumer C 
                    ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
                  LEFT JOIN tb_karyawan D
                    ON A.id_dokter = D.id_karyawan AND A.kode_kantor = D.kode_kantor
                  LEFT JOIN tb_produk E
                    ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
                  LEFT JOIN tb_h_diskon F
                    ON A.id_h_diskon = F.id_h_diskon AND B.kode_kantor = F.kode_kantor
                  WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
                    AND A.id_dokter <> '' AND type_h_penjualan IN('KONSULTASI KECANTIKAN')
                    AND sts_penjualan IN ('SELESAI','PEMBAYARAN')
                    AND E.isProduk IN('JASA') AND A.id_dokter = '".$id_karyawan."' AND LOCATE('INFUS CROMOSOM',E.nama_produk) = 0
                    AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD')
                  ) A
              ) A GROUP BY id_dokter,nama_karyawan,nama_costumer,tgl_h_penjualan
            ) A WHERE subtotal >= 11000000
        ")->result();
      return $data;
    }

    function detail_produk_lebih($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
          SELECT * FROM
          (
            SELECT id_dokter,nama_karyawan,nama_costumer,no_faktur,tgl_h_penjualan,SUM(ROUND(subtotal)) AS subtotal,SUM(subtotal) * 0.05 AS fee
            FROM ( 
             SELECT id_dokter,nama_karyawan,nama_costumer,no_faktur,tgl_h_penjualan,jumlah_konversi * (harga_konversi - diskon_konversi) AS subtotal
              FROM (
                 SELECT A.id_dokter,D.nama_karyawan,A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,
                 A.kode_kantor,E.isProduk,DATEDIFF(tgl_h_penjualan,D.tgl_diterima)/365 AS masa_kerja,B.id_h_diskon,nama_diskon,
                 CASE WHEN B.status_konversi = '*' THEN
                    B.jumlah * B.konversi
                  ELSE
                    B.jumlah / B.konversi
                  END AS jumlah_konversi
                  
                  ,CASE WHEN B.status_konversi = '*' THEN
                    (B.harga / 1.1) / B.konversi
                  ELSE
                    B.harga % B.konversi
                  END AS harga_konversi
                  
                  ,CASE WHEN B.optr_diskon = '%' THEN
                    ((B.harga / 1.1)/100) * B.diskon
                  ELSE
                    B.diskon
                  END AS diskon_konversi,B.optr_diskon,B.diskon
                FROM tb_h_penjualan A
                LEFT JOIN tb_d_penjualan B
                  ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
                LEFT JOIN tb_costumer C 
                  ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
                LEFT JOIN tb_karyawan D
                  ON A.id_dokter = D.id_karyawan AND A.kode_kantor = D.kode_kantor
                LEFT JOIN tb_produk E
                  ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
                LEFT JOIN tb_h_diskon F
                  ON A.id_h_diskon = F.id_h_diskon AND B.kode_kantor = F.kode_kantor
                WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
                  AND A.id_dokter <> '' AND type_h_penjualan IN('KONSULTASI KECANTIKAN')
                  AND sts_penjualan IN ('SELESAI','PEMBAYARAN')
                  AND E.isProduk IN('PRODUK') AND A.id_dokter = '".$id_karyawan."'
                  AND DATEDIFF(A.tgl_h_penjualan,tgl_diterima) >= 730
                  AND E.nama_produk NOT IN('PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','MATERAI','SPON','SPREI+SPONS')
                ) A 
             ) A
             GROUP BY id_dokter,nama_karyawan,nama_costumer,no_faktur,tgl_h_penjualan
          ) A WHERE subtotal >= 1100000
        ")->result();
      return $data;

    }

    function detail_libur($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
          SELECT A.id_karyawan,tanggal,nominal,`event`,aktual_finger,jam_finger
          FROM (
            SELECT A.id_karyawan,tanggal,B.nominal
            FROM tb_jam_kerja_karyawan A
            LEFT JOIN tb_tunjangan_karyawan B
              ON A.id_karyawan = B.id_karyawan 
            LEFT JOIN tb_tunjangan C
              ON B.id_tunjangan = C.id_tunjangan
            WHERE nama_jam NOT IN('LIBUR','CUTI','Izin','Sakit SKD','Sakit Tanpa SKD','Absen','Libur Hari Raya','Libur HR Potong','Dirumahkan')
            AND tanggal BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND C.kode_tunjangan = 'LIBUR'
          ) A LEFT JOIN
          (
            SELECT db_date,`event`
            FROM tb_calendar
            WHERE db_date BETWEEN '".$tgl_from."' AND '".$tgl_to."'
            AND holiday_flag = '1' AND day_name <> 'Sunday'
          ) B ON A.tanggal = B.db_date
          LEFT JOIN
          (
            SELECT id_karyawan,kode_kantor,tanggal AS aktual_finger,GROUP_CONCAT(jam) jam_finger
            FROM (
              SELECT C.id_karyawan,DATE_FORMAT(tanggal,'%Y-%m-%d') tanggal,C.kode_kantor,
                     DATE_FORMAT(tanggal,'%H:%i') jam
              FROM tb_log_absen A
              LEFT JOIN tb_att_employ B
                ON A.id_cabang = B.id_cabang AND A.id_karyawan = B.id_empl
              LEFT JOIN tb_karyawan C
                ON B.id_empl = C.id_mesin_empl AND B.kode_kantor = C.kode_kantor
              WHERE C.id_karyawan = '".$id_karyawan."'
              AND DATE_FORMAT(tanggal,'%Y-%m-%d') BETWEEN '".$tgl_from."' AND '".$tgl_to."'
            ) A GROUP BY A.id_karyawan,kode_kantor,tanggal
          ) C ON A.id_karyawan = C.id_karyawan AND A.tanggal = C.aktual_finger
          WHERE db_date IS NOT NULL AND A.id_karyawan = '".$id_karyawan."'
      ")->result();
      return $data;
    }

    function detail_konsul_lama($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("

         SELECT id_dokter,nama_karyawan,tgl_diterima,id_h_penjualan,id_costumer,tgl_h_penjualan,nama_jasa,nama_produk,total_produk,masa_kerja / 365 AS masa_kerja,nama_costumer,
          CASE WHEN (LOCATE('DOKTER',jenis_tindakan) > 0 OR LOCATE('GABUNGAN',jenis_tindakan) > 0) AND total_produk >= 5 AND masa_kerja >= 365 THEN 5000
         WHEN (LOCATE('DOKTER',jenis_tindakan) > 0 OR LOCATE('GABUNGAN',jenis_tindakan) > 0) AND total_produk >= 5 AND masa_kerja < 365 THEN 2000
         WHEN (LOCATE('TERAPIS',jenis_tindakan) > 0 OR LOCATE('GABUNGAN',jenis_tindakan) > 0) AND total_produk >= 10 AND masa_kerja >= 365 THEN 5000
         WHEN (LOCATE('TERAPIS',jenis_tindakan) > 0 OR LOCATE('GABUNGAN',jenis_tindakan) > 0) AND total_produk >= 10 AND masa_kerja < 365 THEN 2000
          ELSE 
         CASE WHEN (LOCATE('DOKTER',jenis_tindakan) > 0 OR LOCATE('GABUNGAN',jenis_tindakan) > 0) AND total_produk < 5 AND masa_kerja >= 365 THEN 2500
        WHEN (LOCATE('DOKTER',jenis_tindakan) > 0 OR LOCATE('GABUNGAN',jenis_tindakan) > 0) AND total_produk < 5 AND masa_kerja < 365 THEN 1000
        WHEN (LOCATE('TERAPIS',jenis_tindakan) > 0 OR LOCATE('GABUNGAN',jenis_tindakan) > 0) AND total_produk < 10 AND masa_kerja >= 365 THEN 2500
        WHEN (LOCATE('TERAPIS',jenis_tindakan) > 0 OR LOCATE('GABUNGAN',jenis_tindakan) > 0) AND total_produk < 10 AND masa_kerja < 365 THEN 1000
         ELSE 0 END
          END AS INS,jenis_tindakan
             FROM 
            (
        SELECT  id_dokter,nama_karyawan,tgl_diterima,id_h_penjualan,id_costumer,tgl_h_penjualan,
          GROUP_CONCAT(nama_jasa SEPARATOR ', ') nama_jasa,
          GROUP_CONCAT(nama_produk SEPARATOR ', ') nama_produk,
          GROUP_CONCAT(jenis_tindakan SEPARATOR ', ') jenis_tindakan,
          SUM(total_jasa) total_jasa,SUM(total_produk) total_produk,masa_kerja,nama_costumer
        FROM
        (
          SELECT  id_dokter,nama_karyawan,tgl_diterima,id_h_penjualan,id_costumer,tgl_h_penjualan,
            CASE WHEN isProduk = 'JASA' THEN nama_produk ELSE '' END AS nama_jasa,
            CASE WHEN isProduk = 'PRODUK' THEN nama_produk ELSE '' END AS nama_produk,
            CASE WHEN isProduk = 'JASA' THEN jumlah ELSE '' END AS total_jasa,
            CASE WHEN isProduk = 'PRODUK' THEN jumlah ELSE '' END AS total_produk,masa_kerja,jenis_tindakan,nama_costumer
          FROM
          (
            SELECT A.id_dokter,D.nama_karyawan,tgl_diterima,A.id_h_penjualan,A.id_costumer,tgl_h_penjualan,GROUP_CONCAT(E.nama_produk SEPARATOR ', ') nama_produk,
             SUM(B.jumlah) AS jumlah,A.kode_kantor,E.isProduk,DATEDIFF(tgl_h_penjualan,tgl_diterima) AS masa_kerja,COALESCE(jenis_tindakan,'') jenis_tindakan,C.nama_lengkap AS nama_costumer
            FROM tb_h_penjualan A
            LEFT JOIN tb_d_penjualan B
        ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
            LEFT JOIN tb_costumer C 
        ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
            LEFT JOIN tb_karyawan D
        ON A.id_dokter = D.id_karyawan AND A.kode_kantor = D.kode_kantor
            LEFT JOIN tb_produk E
        ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
            WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
            AND id_dokter <> '' AND type_h_penjualan = 'KONSULTASI KECANTIKAN'
            AND tgl_h_penjualan <> tgl_registrasi AND sts_penjualan IN ('SELESAI','PEMBAYARAN') 
            AND A.id_dokter = '".$id_karyawan."' AND E.isProduk IN('JASA','PRODUK')
            AND E.nama_produk NOT IN('PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','SPON','MATERAI','ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','SPREI+SPONS')
            GROUP BY A.id_dokter,D.nama_karyawan,tgl_diterima,A.id_h_penjualan,A.id_costumer,tgl_h_penjualan,E.isProduk,COALESCE(jenis_tindakan,''),C.nama_lengkap
          ) A 
            ) A GROUP BY id_dokter,nama_karyawan,tgl_diterima,id_h_penjualan,id_costumer,tgl_h_penjualan,masa_kerja,nama_costumer
          ) A WHERE nama_jasa <> ''
      ")->result();

      return $data;
    }

    function detail_konsul_baru($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
        SELECT id_dokter,nama_karyawan,tgl_diterima,id_h_penjualan,id_costumer,tgl_h_penjualan,nama_jasa,nama_produk,total_produk,masa_kerja / 365 AS masa_kerja,nama_costumer,nama_costumer,
        CASE WHEN LOCATE('DETOX',nama_jasa) > 0 AND (LOCATE('PEEL',nama_jasa) > 0 OR LOCATE('MICRODERMABRASI',nama_jasa) > 0) AND total_produk >= 10 AND masa_kerja >= 365 THEN 10000 
       WHEN LOCATE('DETOX',nama_jasa) > 0 AND (LOCATE('PEEL',nama_jasa) > 0 OR LOCATE('MICRODERMABRASI',nama_jasa) > 0) AND total_produk >= 10 AND masa_kerja < 365 THEN 5000
        ELSE
       CASE WHEN LOCATE('DETOX',nama_jasa) > 0 AND (LOCATE('PEEL',nama_jasa) > 0 OR LOCATE('MICRODERMABRASI',nama_jasa) > 0) AND total_produk >= 5 AND total_produk < 10 AND masa_kerja >= 365 THEN 5000 
      WHEN LOCATE('DETOX',nama_jasa) > 0 AND (LOCATE('PEEL',nama_jasa) > 0 OR LOCATE('MICRODERMABRASI',nama_jasa) > 0) AND total_produk >= 5 AND total_produk < 10 AND masa_kerja < 365 THEN 2500 
       ELSE 0 END
        END AS INS
        FROM 
        (
      SELECT  id_dokter,nama_karyawan,tgl_diterima,id_h_penjualan,id_costumer,tgl_h_penjualan,
        GROUP_CONCAT(nama_jasa SEPARATOR ', ') nama_jasa,
        GROUP_CONCAT(nama_produk SEPARATOR ', ') nama_produk,
        SUM(total_jasa) total_jasa,SUM(total_produk) total_produk,masa_kerja,nama_costumer
      FROM
      (
        SELECT  id_dokter,nama_karyawan,tgl_diterima,id_h_penjualan,id_costumer,tgl_h_penjualan,
          CASE WHEN isProduk = 'JASA' THEN nama_produk ELSE '' END AS nama_jasa,
          CASE WHEN isProduk = 'PRODUK' THEN nama_produk ELSE '' END AS nama_produk,
          CASE WHEN isProduk = 'JASA' THEN jumlah ELSE '' END AS total_jasa,
          CASE WHEN isProduk = 'PRODUK' THEN jumlah ELSE '' END AS total_produk,masa_kerja,nama_costumer
        FROM
        (
          SELECT A.id_dokter,D.nama_karyawan,tgl_diterima,A.id_h_penjualan,A.id_costumer,tgl_h_penjualan,GROUP_CONCAT(E.nama_produk SEPARATOR ', ') nama_produk,
           SUM(B.jumlah) AS jumlah,A.kode_kantor,E.isProduk,DATEDIFF(tgl_h_penjualan,tgl_diterima) AS masa_kerja,C.nama_lengkap AS nama_costumer
          FROM tb_h_penjualan A
          LEFT JOIN tb_d_penjualan B
      ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
          LEFT JOIN tb_costumer C 
      ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
          LEFT JOIN tb_karyawan D
      ON A.id_dokter = D.id_karyawan AND A.kode_kantor = D.kode_kantor
          LEFT JOIN tb_produk E
      ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
      WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
          AND id_dokter <> '' AND type_h_penjualan = 'KONSULTASI KECANTIKAN'
          AND tgl_h_penjualan = tgl_registrasi AND sts_penjualan IN ('SELESAI','PEMBAYARAN')
          AND A.id_dokter = '".$id_karyawan."' AND E.isProduk IN('JASA','PRODUK')
          AND E.nama_produk NOT IN('PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','SPON','MATERAI','ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','SPREI+SPONS')
          GROUP BY A.id_dokter,D.nama_karyawan,tgl_diterima,A.id_h_penjualan,A.id_costumer,tgl_h_penjualan,E.isProduk,C.nama_lengkap
        ) A 
          ) A GROUP BY id_dokter,nama_karyawan,tgl_diterima,id_h_penjualan,id_costumer,tgl_h_penjualan,masa_kerja,nama_costumer
        ) A
      ")->result();

      return $data;
    }

    function hitung_ins_dokter_tahap1_lama($tgl_from,$tgl_to,$periode)
    {
       $this->db->query("    
          INSERT INTO tb_d_tampung_gaji (
                id_karyawan,
                periode,
                kode_akun,
                nama_akun,
                jenis_akun,
                keterangan,
                nominal,
                user_ins,
                kode_kantor
              )
          SELECT id_dok_total AS id_karyawan,'".$periode."' AS periode,'TINDAKAN' AS kode,'Insentif Tindakan' nama_akun,'DEBIT' AS jenis,'' AS keterangan, 
            SUM(CASE WHEN group_produk = 'JASA,PRODUK' THEN harga * 0.01
            WHEN group_produk = 'JASA' THEN harga * 0.0075
            WHEN group_produk = 'PRODUK' THEN harga * 0.0025 ELSE 0 END) AS fee,'".$this->session->userdata('ses_id_karyawan')."' AS user_ins,'' kode_kantor

          FROM (
            SELECT A.no_faktur, ROUND(SUM(jumlah_konversi * (harga_konversi - diskon_konversi))) AS harga,CTX2,id_dok_total,
              GROUP_CONCAT(DISTINCT(isProduk),'') AS group_produk,dokter_konsul
            FROM (
              SELECT A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,
                 A.kode_kantor,E.isProduk,A.id_dokter AS dokter_konsul,
                 CASE WHEN B.status_konversi = '*' THEN
                    B.jumlah * B.konversi
                  ELSE
                    B.jumlah / B.konversi
                  END AS jumlah_konversi
                  ,CASE WHEN B.status_konversi = '*' THEN (B.harga / 1.1) / B.konversi ELSE B.harga % B.konversi END AS harga_konversi
                  ,CASE WHEN B.optr_diskon = '%' THEN ((B.harga / 1.1)/100) * B.diskon ELSE B.diskon
                  END AS diskon_konversi,B.optr_diskon,B.diskon,B.id_dok,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX
              FROM tb_h_penjualan A
              LEFT JOIN tb_d_penjualan B
                 ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
              LEFT JOIN tb_produk E
                 ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
              WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
              AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                    'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
            ) A CROSS JOIN (
              SELECT no_faktur,SUM(CTX) AS CTX2,id_dok_total
              FROM (
                SELECT A.no_faktur,CASE WHEN id_dok = '' THEN A.id_dokter ELSE id_dok END AS id_dok_total,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX
                FROM tb_h_penjualan A
                LEFT JOIN tb_d_penjualan B
                   ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
                LEFT JOIN tb_produk E
                   ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
                WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
                AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                    'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
              ) AA GROUP BY no_faktur
            ) B ON A.no_faktur = B.no_faktur
            WHERE CTX2 <> 0 -- and id_dok_total = 'ONBDG2020060500005'
            GROUP BY A.no_faktur,CTX2,id_dok_total,dokter_konsul
          ) A LEFT JOIN tb_gaji_pokok B ON A.id_dok_total = B.id_karyawan
          WHERE rumus_gaji = '2'
          GROUP BY id_dok_total
       ");
    }

    function hitung_ins_dokter_tahap1_baru($tgl_from,$tgl_to,$periode)
    {
       $this->db->query("    
          INSERT INTO tb_d_tampung_gaji (
                id_karyawan,
                periode,
                kode_akun,
                nama_akun,
                jenis_akun,
                keterangan,
                nominal,
                user_ins,
                kode_kantor
              )
          SELECT id_dok_total AS id_karyawan,'".$periode."' AS periode,'TINDAKAN' AS kode,'Insentif Tindakan' nama_akun,'DEBIT' AS jenis,'' AS keterangan, 
            SUM(CASE WHEN group_produk = 'JASA,PRODUK' THEN harga * 0.01
            WHEN group_produk = 'JASA' THEN harga * 0.0075
            WHEN group_produk = 'PRODUK' THEN harga * 0.0025 ELSE 0 END) / 2 AS fee,'".$this->session->userdata('ses_id_karyawan')."' AS user_ins,'' kode_kantor
          FROM (
            SELECT A.no_faktur, ROUND(SUM(jumlah_konversi * (harga_konversi - diskon_konversi))) AS harga,CTX2,id_dok_total,
              GROUP_CONCAT(DISTINCT(isProduk),'') AS group_produk,dokter_konsul
            FROM (
              SELECT A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,
                 A.kode_kantor,E.isProduk,A.id_dokter AS dokter_konsul,
                 CASE WHEN B.status_konversi = '*' THEN
                    B.jumlah * B.konversi
                  ELSE
                    B.jumlah / B.konversi
                  END AS jumlah_konversi
                  ,CASE WHEN B.status_konversi = '*' THEN (B.harga / 1.1) / B.konversi ELSE B.harga % B.konversi END AS harga_konversi
                  ,CASE WHEN B.optr_diskon = '%' THEN ((B.harga / 1.1)/100) * B.diskon ELSE B.diskon
                  END AS diskon_konversi,B.optr_diskon,B.diskon,B.id_dok,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX
              FROM tb_h_penjualan A
              LEFT JOIN tb_d_penjualan B
                 ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
              LEFT JOIN tb_produk E
                 ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
              WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
              AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                    'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
            ) A CROSS JOIN (
              SELECT no_faktur,SUM(CTX) AS CTX2,id_dok_total
              FROM (
                SELECT A.no_faktur,CASE WHEN id_dok = '' THEN A.id_dokter ELSE id_dok END AS id_dok_total,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX
                FROM tb_h_penjualan A
                LEFT JOIN tb_d_penjualan B
                   ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
                LEFT JOIN tb_produk E
                   ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
                WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
                AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                    'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
              ) AA GROUP BY no_faktur
            ) B ON A.no_faktur = B.no_faktur
            WHERE CTX2 <> 0 -- and id_dok_total = 'ONBDG2020060500005'
            GROUP BY A.no_faktur,CTX2,id_dok_total,dokter_konsul
          ) A LEFT JOIN tb_gaji_pokok B ON A.id_dok_total = B.id_karyawan
          WHERE rumus_gaji = '1'
          GROUP BY id_dok_total
       ");
    }

    function hitung_ins_dokter_tahap2($tgl_from,$tgl_to,$periode)
    {
       $this->db->query("    
          INSERT INTO tb_d_tampung_gaji (
                id_karyawan,
                periode,
                kode_akun,
                nama_akun,
                jenis_akun,
                keterangan,
                nominal,
                user_ins,
                kode_kantor
              )
          SELECT id_dok_total AS id_karyawan,'".$periode."' AS periode,'TINDAKAN' AS kode,'Insentif Tindakan' nama_akun,'DEBIT' AS jenis,'' AS keterangan, 
            SUM(CASE WHEN group_produk = 'JASA,PRODUK' THEN harga * 0.02
            WHEN group_produk = 'JASA' THEN harga * 0.015
            WHEN group_produk = 'PRODUK' THEN harga * 0.005 ELSE 0 END) AS fee,'".$this->session->userdata('ses_id_karyawan')."' AS user_ins,'' kode_kantor

          FROM (
            SELECT A.no_faktur, ROUND(SUM(jumlah_konversi * (harga_konversi - diskon_konversi))) AS harga,CTX2,id_dok_total,
              GROUP_CONCAT(DISTINCT(isProduk),'') AS group_produk,dokter_konsul
            FROM (
              SELECT A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,
                 A.kode_kantor,E.isProduk,A.id_dokter AS dokter_konsul,
                 CASE WHEN B.status_konversi = '*' THEN
                    B.jumlah * B.konversi
                  ELSE
                    B.jumlah / B.konversi
                  END AS jumlah_konversi
                  ,CASE WHEN B.status_konversi = '*' THEN (B.harga / 1.1) / B.konversi ELSE B.harga % B.konversi END AS harga_konversi
                  ,CASE WHEN B.optr_diskon = '%' THEN ((B.harga / 1.1)/100) * B.diskon ELSE B.diskon
                  END AS diskon_konversi,B.optr_diskon,B.diskon,B.id_dok,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX
              FROM tb_h_penjualan A
              LEFT JOIN tb_d_penjualan B
                 ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
              LEFT JOIN tb_produk E
                 ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
              WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
              AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                    'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
            ) A CROSS JOIN (
              SELECT no_faktur,SUM(CTX) AS CTX2,id_dok_total
              FROM (
                SELECT A.no_faktur,CASE WHEN id_dok = '' THEN A.id_dokter ELSE id_dok END AS id_dok_total,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX
                FROM tb_h_penjualan A
                LEFT JOIN tb_d_penjualan B
                   ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
                LEFT JOIN tb_produk E
                   ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
                WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
                AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                    'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
              ) AA GROUP BY no_faktur
            ) B ON A.no_faktur = B.no_faktur
            WHERE CTX2 <> 0 -- and id_dok_total = 'ONBDG2020060500005'
            GROUP BY A.no_faktur,CTX2,id_dok_total,dokter_konsul
          ) A LEFT JOIN tb_gaji_pokok B ON A.id_dok_total = B.id_karyawan
          WHERE rumus_gaji = '3'
          GROUP BY id_dok_total
       ");
    }

    
    function detail_ins_dokter_tahap1_lama($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
        SELECT no_faktur,nama_costumer,tgl_h_penjualan,group_produk,harga,
            CASE WHEN group_produk = 'JASA,PRODUK' THEN '1%'
              WHEN group_produk = 'JASA' THEN '0.75%'
              WHEN group_produk = 'PRODUK' THEN '0.25%' ELSE 0 END AS fee,
            SUM(CASE WHEN group_produk = 'JASA,PRODUK' THEN harga * 0.01
            WHEN group_produk = 'JASA' THEN harga * 0.0075
            WHEN group_produk = 'PRODUK' THEN harga * 0.0025 ELSE 0 END) AS fee_nominal
          FROM (
            SELECT A.no_faktur,nama_costumer,tgl_h_penjualan, ROUND(SUM(jumlah_konversi * (harga_konversi - diskon_konversi))) AS harga,CTX2,id_dok_total,
              GROUP_CONCAT(DISTINCT(isProduk),'') AS group_produk,dokter_konsul
            FROM (
              SELECT A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,
                 A.kode_kantor,E.isProduk,A.id_dokter AS dokter_konsul,
                 CASE WHEN B.status_konversi = '*' THEN
                    B.jumlah * B.konversi
                  ELSE
                    B.jumlah / B.konversi
                  END AS jumlah_konversi
                  ,CASE WHEN B.status_konversi = '*' THEN (B.harga / 1.1) / B.konversi ELSE B.harga % B.konversi END AS harga_konversi
                  ,CASE WHEN B.optr_diskon = '%' THEN ((B.harga / 1.1)/100) * B.diskon ELSE B.diskon
                  END AS diskon_konversi,B.optr_diskon,B.diskon,B.id_dok,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX
              FROM tb_h_penjualan A
              LEFT JOIN tb_d_penjualan B
                 ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
              LEFT JOIN tb_produk E
                 ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
              WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
              AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                    'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
            ) A CROSS JOIN (
              SELECT no_faktur,SUM(CTX) AS CTX2,id_dok_total
              FROM (
                SELECT A.no_faktur,CASE WHEN id_dok = '' THEN A.id_dokter ELSE id_dok END AS id_dok_total,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX
                FROM tb_h_penjualan A
                LEFT JOIN tb_d_penjualan B
                   ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
                LEFT JOIN tb_produk E
                   ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
                WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
                AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                    'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
              ) AA GROUP BY no_faktur
            ) B ON A.no_faktur = B.no_faktur
            WHERE CTX2 <> 0 AND id_dok_total = '".$id_karyawan."'
            GROUP BY A.no_faktur,CTX2,id_dok_total,dokter_konsul,tgl_h_penjualan
          ) A GROUP BY no_faktur,nama_costumer,group_produk,harga,tgl_h_penjualan
      ")->result();

      return $data;
    }

    function detail_ins_dokter_tahap1_baru($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
        SELECT no_faktur,nama_costumer,tgl_h_penjualan,group_produk,harga,
            CASE WHEN group_produk = 'JASA,PRODUK' THEN '1% /2'
              WHEN group_produk = 'JASA' THEN '0.75% /2'
              WHEN group_produk = 'PRODUK' THEN '0.25% /2 ' ELSE 0 END AS fee,
            SUM(CASE WHEN group_produk = 'JASA,PRODUK' THEN harga * 0.01
            WHEN group_produk = 'JASA' THEN harga * 0.0075
            WHEN group_produk = 'PRODUK' THEN harga * 0.0025 ELSE 0 END) /2 AS fee_nominal
          FROM (
            SELECT A.no_faktur,nama_costumer,tgl_h_penjualan, ROUND(SUM(jumlah_konversi * (harga_konversi - diskon_konversi))) AS harga,CTX2,id_dok_total,
              GROUP_CONCAT(DISTINCT(isProduk),'') AS group_produk,dokter_konsul
            FROM (
              SELECT A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,
                 A.kode_kantor,E.isProduk,A.id_dokter AS dokter_konsul,
                 CASE WHEN B.status_konversi = '*' THEN
                    B.jumlah * B.konversi
                  ELSE
                    B.jumlah / B.konversi
                  END AS jumlah_konversi
                  ,CASE WHEN B.status_konversi = '*' THEN (B.harga / 1.1) / B.konversi ELSE B.harga % B.konversi END AS harga_konversi
                  ,CASE WHEN B.optr_diskon = '%' THEN ((B.harga / 1.1)/100) * B.diskon ELSE B.diskon
                  END AS diskon_konversi,B.optr_diskon,B.diskon,B.id_dok,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX
              FROM tb_h_penjualan A
              LEFT JOIN tb_d_penjualan B
                 ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
              LEFT JOIN tb_produk E
                 ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
              WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
              AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                    'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
            ) A CROSS JOIN (
              SELECT no_faktur,SUM(CTX) AS CTX2,id_dok_total
              FROM (
                SELECT A.no_faktur,CASE WHEN id_dok = '' THEN A.id_dokter ELSE id_dok END AS id_dok_total,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX
                FROM tb_h_penjualan A
                LEFT JOIN tb_d_penjualan B
                   ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
                LEFT JOIN tb_produk E
                   ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
                WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
                AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                    'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
              ) AA GROUP BY no_faktur
            ) B ON A.no_faktur = B.no_faktur
            WHERE CTX2 <> 0 AND id_dok_total = '".$id_karyawan."'
            GROUP BY A.no_faktur,CTX2,id_dok_total,dokter_konsul,tgl_h_penjualan
          ) A GROUP BY no_faktur,nama_costumer,group_produk,harga,tgl_h_penjualan
      ")->result();

      return $data;
    }

    function detail_ins_dokter_tahap2($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
        SELECT no_faktur,nama_costumer,tgl_h_penjualan,group_produk,harga,
            CASE WHEN group_produk = 'JASA,PRODUK' THEN '2%'
              WHEN group_produk = 'JASA' THEN '1.50%'
              WHEN group_produk = 'PRODUK' THEN '0.50%' ELSE 0 END AS fee,
            SUM(CASE WHEN group_produk = 'JASA,PRODUK' THEN harga * 0.02
            WHEN group_produk = 'JASA' THEN harga * 0.015
            WHEN group_produk = 'PRODUK' THEN harga * 0.005 ELSE 0 END) AS fee_nominal
          FROM (
            SELECT A.no_faktur,nama_costumer,tgl_h_penjualan, ROUND(SUM(jumlah_konversi * (harga_konversi - diskon_konversi))) AS harga,CTX2,id_dok_total,
              GROUP_CONCAT(DISTINCT(isProduk),'') AS group_produk,dokter_konsul
            FROM (
              SELECT A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,
                 A.kode_kantor,E.isProduk,A.id_dokter AS dokter_konsul,
                 CASE WHEN B.status_konversi = '*' THEN
                    B.jumlah * B.konversi
                  ELSE
                    B.jumlah / B.konversi
                  END AS jumlah_konversi
                  ,CASE WHEN B.status_konversi = '*' THEN (B.harga / 1.1) / B.konversi ELSE B.harga % B.konversi END AS harga_konversi
                  ,CASE WHEN B.optr_diskon = '%' THEN ((B.harga / 1.1)/100) * B.diskon ELSE B.diskon
                  END AS diskon_konversi,B.optr_diskon,B.diskon,B.id_dok,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX
              FROM tb_h_penjualan A
              LEFT JOIN tb_d_penjualan B
                 ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
              LEFT JOIN tb_produk E
                 ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
              WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
              AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                    'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
            ) A CROSS JOIN (
              SELECT no_faktur,SUM(CTX) AS CTX2,id_dok_total
              FROM (
                SELECT A.no_faktur,CASE WHEN id_dok = '' THEN A.id_dokter ELSE id_dok END AS id_dok_total,CASE WHEN B.id_dok <> '' THEN 1 ELSE 0 END AS CTX
                FROM tb_h_penjualan A
                LEFT JOIN tb_d_penjualan B
                   ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
                LEFT JOIN tb_produk E
                   ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
                WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') -- AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
                AND B.isProduk IN('JASA','PRODUK') AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD',
                    'MATERAI','PAPER BAG BESAR','PAPER BAG SEDANG','PAPER BAG KECIL','ONGKIR','SPON','FACIAL SET','SPREI+SPONS')
              ) AA GROUP BY no_faktur
            ) B ON A.no_faktur = B.no_faktur
            WHERE CTX2 <> 0 AND id_dok_total = '".$id_karyawan."'
            GROUP BY A.no_faktur,CTX2,id_dok_total,dokter_konsul,tgl_h_penjualan
          ) A GROUP BY no_faktur,nama_costumer,group_produk,harga,tgl_h_penjualan
      ")->result();

      return $data;
    }

    // function hitung_ins_dokter_new($tgl_from,$tgl_to,$periode)
    // {
    //   $data = $this->db->query("
    //     INSERT INTO tb_d_tampung_gaji (
    //                 id_karyawan,
    //                 periode,
    //                 kode_akun,
    //                 nama_akun,
    //                 jenis_akun,
    //                 keterangan,
    //                 nominal,
    //                 user_ins,
    //                 kode_kantor
    //               )
    //     SELECT A.id_karyawan,'".$periode."' AS periode,'TINDAKAN' AS kode,'Insentif Tindakan' nama_akun,'DEBIT' AS jenis,'' AS keterangan, 
    //             SUM(fee_tindakan) AS fee_tindakan,'".$this->session->userdata('ses_id_karyawan')."' AS user_ins,'' kode_kantor
    //     FROM (
    //     SELECT id_karyawan,nama_produk,harga_konversi,jumlah_konversi,
    //            CASE WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN harga_konversi * 0.005
    //           WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN harga_konversi * 0.01
    //           WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 7 AND 9 THEN harga_konversi * 0.015
    //           WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 10 AND 12 THEN harga_konversi * 0.02
    //           WHEN harga_konversi <= 1000000 AND jumlah_konversi > 12 THEN harga_konversi * 0.025
    //           WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN harga_konversi * 0.01
    //           WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN harga_konversi * 0.015
    //           WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 7 AND 9 THEN harga_konversi * 0.02
    //           WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi > 9 THEN harga_konversi * 0.025
    //           WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN harga_konversi * 0.015
    //           WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN harga_konversi * 0.02
    //           WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi > 6 THEN harga_konversi * 0.025
    //           WHEN harga_konversi > 5000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN harga_konversi * 0.02
    //           WHEN harga_konversi > 5000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN harga_konversi * 0.025
    //           WHEN harga_konversi > 5000000 AND jumlah_konversi > 6 THEN harga_konversi * 0.03 END AS fee_tindakan
              
    //     FROM
    //     (
    //       SELECT id_karyawan,CASE WHEN nama_produk IN('GLAFIDSYA ACNE GLOW PEEL','GLAFIDSYA SUPER ACNE PEEL','GLAFIDSYA ANTI FLEX PEEL','GLAFIDSYA GLOWING PEEL','GLAFIDSYA PORI PEEL') THEN 'GLAFIDSYA PEEL'
    //             WHEN nama_produk IN('FACIAL PORI PEEL','FACIAL GLOWING PEEL','FACIAL FLEX PEEL','FACIAL ANTI AGING PEEL','FACIAL ACNE PEEL') THEN 'FACIAL PEEL' 
    //             ELSE nama_produk END AS nama_produk,
    //              SUM(CASE WHEN nama_produk LIKE '%GLAFIDSYA%' THEN jumlah_konversi ELSE jumlah_konversi END) AS jumlah_konversi,
    //              harga_konversi
    //       FROM
    //       (
    //         SELECT id_karyawan,nama_produk,
    //           CASE WHEN LOCATE('BUY 1 GET 1',nama_produk) > 0 OR diskon = 100 THEN 
    //             ROUND((harga_konversi * 0.5),-3)
    //                ELSE 
    //             ROUND((harga_konversi - diskon_konversi),-3)
    //           END AS harga_konversi,jumlah_konversi,nama_diskon
    //         FROM (
    //           SELECT B.id_dok AS id_karyawan,D.nama_karyawan,A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,
    //              A.kode_kantor,E.isProduk,DATEDIFF(tgl_h_penjualan,D.tgl_diterima)/365 AS masa_kerja,B.id_h_diskon,nama_diskon,
    //              CASE WHEN B.status_konversi = '*' THEN
    //                 B.jumlah * B.konversi
    //               ELSE
    //                 B.jumlah / B.konversi
    //               END AS jumlah_konversi
    //               ,CASE WHEN B.status_konversi = '*' THEN
    //                 (B.harga / 1.1) / B.konversi
    //               ELSE
    //                 B.harga % B.konversi
    //               END AS harga_konversi
    //               ,CASE WHEN B.optr_diskon = '%' THEN
    //                 ((B.harga / 1.1)/100) * B.diskon
    //               ELSE
    //                 B.diskon
    //               END AS diskon_konversi,B.optr_diskon,B.diskon 
    //           FROM tb_h_penjualan A
    //           LEFT JOIN tb_d_penjualan B
    //              ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
    //           LEFT JOIN tb_costumer C 
    //              ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
    //           LEFT JOIN tb_karyawan D
    //              ON A.id_dokter = D.id_karyawan AND A.kode_kantor = D.kode_kantor
    //           LEFT JOIN tb_produk E
    //              ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
    //           LEFT JOIN tb_h_diskon F
    //              ON F.id_h_diskon = CASE WHEN B.id_d_diskon2 = '' THEN B.id_h_diskon
    //                                   WHEN B.id_h_diskon = '' THEN A.id_h_diskon ELSE '' END 
    //                 AND B.kode_kantor = F.kode_kantor
    //           LEFT JOIN tb_gaji_pokok G
    //              ON B.id_dok = G.id_karyawan
    //           WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
    //           AND B.id_dok <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
    //           AND E.isProduk IN('JASA')
    //           AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD')
    //           AND rumus_gaji = '1'
    //         ) A 
    //       ) A GROUP BY id_karyawan,CASE WHEN nama_produk IN('GLAFIDSYA ACNE GLOW PEEL','GLAFIDSYA SUPER ACNE PEEL','GLAFIDSYA ANTI FLEX PEEL','GLAFIDSYA GLOWING PEEL','GLAFIDSYA PORI PEEL') THEN 'GLAFIDSYA PEEL'
    //             WHEN nama_produk IN('FACIAL PORI PEEL','FACIAL GLOWING PEEL','FACIAL FLEX PEEL','FACIAL ANTI AGING PEEL','FACIAL ACNE PEEL') THEN 'FACIAL PEEL' 
    //             ELSE nama_produk END,harga_konversi
    //     ) A
    //     ) A GROUP BY id_karyawan
    //   ");
    // }

    // function detail_ins_dokter_new($id_karyawan,$tgl_from,$tgl_to)
    // {
    //   $data = $this->db->query("
    //     SELECT id_karyawan,nama_produk,harga_konversi,jumlah_konversi,COALESCE(nama_diskon,'') AS nama_diskon,
    //           CASE WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN 1 
    //             WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN 2
    //             WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 7 AND 9 THEN 3
    //             WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 10 AND 12 THEN 4
    //             WHEN harga_konversi <= 1000000 AND jumlah_konversi > 12 THEN 5
    //             WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN 2
    //             WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN 3
    //             WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 7 AND 9 THEN 4
    //             WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi > 9 THEN 5
    //             WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN 3
    //             WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN 4
    //             WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi > 6 THEN 5
    //             WHEN harga_konversi > 5000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN 4
    //             WHEN harga_konversi > 5000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN 5
    //             WHEN harga_konversi > 5000000 AND jumlah_konversi > 6 THEN 6 END AS fee,
    //           CASE WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN harga_konversi * jumlah_konversi * 0.005 
    //             WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN harga_konversi * jumlah_konversi * 0.01
    //             WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 7 AND 9 THEN harga_konversi * jumlah_konversi * 0.015
    //             WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 10 AND 12 THEN harga_konversi * jumlah_konversi * 0.02
    //             WHEN harga_konversi <= 1000000 AND jumlah_konversi > 12 THEN harga_konversi * jumlah_konversi * 0.025
    //             WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN harga_konversi * jumlah_konversi * 0.01
    //             WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN harga_konversi * jumlah_konversi * 0.015
    //             WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 7 AND 9 THEN harga_konversi * jumlah_konversi * 0.02
    //             WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi > 9 THEN harga_konversi * jumlah_konversi * 0.025
    //             WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN harga_konversi * jumlah_konversi * 0.015
    //             WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN harga_konversi * jumlah_konversi * 0.02
    //             WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi > 6 THEN harga_konversi * jumlah_konversi * 0.025
    //             WHEN harga_konversi > 5000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN harga_konversi * jumlah_konversi * 0.02
    //             WHEN harga_konversi > 5000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN harga_konversi * jumlah_konversi * 0.025
    //             WHEN harga_konversi > 5000000 AND jumlah_konversi > 6 THEN harga_konversi * jumlah_konversi * 0.03 END AS fee_tindakan
              
    //           FROM
    //           (
    //             SELECT id_karyawan,CASE WHEN nama_produk IN('GLAFIDSYA ACNE GLOW PEEL','GLAFIDSYA SUPER ACNE PEEL','GLAFIDSYA ANTI FLEX PEEL','GLAFIDSYA GLOWING PEEL','GLAFIDSYA PORI PEEL') THEN 'GLAFIDSYA PEEL'
    //             WHEN nama_produk IN('FACIAL PORI PEEL','FACIAL GLOWING PEEL','FACIAL FLEX PEEL','FACIAL ANTI AGING PEEL','FACIAL ACNE PEEL') THEN 'FACIAL PEEL' 
    //             ELSE nama_produk END AS nama_produk,
    //              SUM(CASE WHEN nama_produk LIKE '%GLAFIDSYA%' THEN jumlah_konversi ELSE jumlah_konversi END) AS jumlah_konversi,nama_diskon,
    //              harga_konversi
    //             FROM
    //             (
    //               SELECT id_karyawan,nama_produk,
    //           CASE WHEN LOCATE('BUY 1 GET 1',nama_produk) > 0 OR diskon = 100 THEN 
    //             ROUND((harga_konversi),-3)
    //                ELSE 
    //             ROUND((harga_konversi - diskon_konversi),-3)
    //           END AS harga_konversi,jumlah_konversi,nama_diskon
    //               FROM (
    //           SELECT B.id_dok AS id_karyawan,A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,
    //              A.kode_kantor,E.isProduk,B.id_h_diskon,nama_diskon,
    //              CASE WHEN B.status_konversi = '*' THEN
    //                 B.jumlah * B.konversi
    //               ELSE
    //                 B.jumlah / B.konversi
    //               END AS jumlah_konversi
    //               ,CASE WHEN nama_produk IN('FACIAL ACNE PEEL','FACIAL FLEX PEEL','FACIAL GLOWING PEEL') THEN 700000
    //                     WHEN nama_produk LIKE '%FACIAL SUPER REJUVE%' THEN 401000
    //                ELSE 
    //               CASE WHEN B.status_konversi = '*' THEN (B.harga / 1.1) / B.konversi ELSE B.harga % B.konversi END
    //               END AS harga_konversi
    //               ,CASE WHEN B.optr_diskon = '%' THEN
    //                 ((B.harga / 1.1)/100) * B.diskon
    //               ELSE
    //                 B.diskon
    //               END AS diskon_konversi,B.optr_diskon,B.diskon 
    //           FROM tb_h_penjualan A
    //           LEFT JOIN tb_d_penjualan B
    //              ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
    //           LEFT JOIN tb_costumer C 
    //              ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
    //           LEFT JOIN tb_produk E
    //              ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
    //           LEFT JOIN tb_h_diskon F
    //              ON F.id_h_diskon = CASE WHEN B.id_d_diskon2 = '' THEN B.id_h_diskon
    //                                   WHEN B.id_h_diskon = '' THEN A.id_h_diskon ELSE '' END 
    //                 AND B.kode_kantor = F.kode_kantor
    //           WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
    //           AND B.id_dok <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
    //           AND E.isProduk IN('JASA') AND B.id_dok = '".$id_karyawan."'
    //           AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD')
    //               ) A 
    //             ) A GROUP BY id_karyawan,CASE WHEN nama_produk IN('GLAFIDSYA ACNE GLOW PEEL','GLAFIDSYA SUPER ACNE PEEL','GLAFIDSYA ANTI FLEX PEEL','GLAFIDSYA GLOWING PEEL','GLAFIDSYA PORI PEEL') THEN 'GLAFIDSYA PEEL'
    //             WHEN nama_produk IN('FACIAL PORI PEEL','FACIAL GLOWING PEEL','FACIAL FLEX PEEL','FACIAL ANTI AGING PEEL','FACIAL ACNE PEEL') THEN 'FACIAL PEEL' 
    //             ELSE nama_produk END,harga_konversi,nama_diskon
    //           ) A
    //           ORDER BY nama_produk,nama_diskon
    //   ")->result();

    //   return $data;
    // }

    // function hitung_ins_dokter_middle($tgl_from,$tgl_to,$periode)
    // {
    //   $data = $this->db->query("
    //     INSERT INTO tb_d_tampung_gaji (
    //                 id_karyawan,
    //                 periode,
    //                 kode_akun,
    //                 nama_akun,
    //                 jenis_akun,
    //                 keterangan,
    //                 nominal,
    //                 user_ins,
    //                 kode_kantor
    //               )
    //     SELECT A.id_karyawan,'".$periode."' AS periode,'TINDAKAN' AS kode,'Insentif Tindakan' nama_akun,'DEBIT' AS jenis,'' AS keterangan, 
    //             SUM(fee_tindakan) AS fee_tindakan,'".$this->session->userdata('ses_id_karyawan')."' AS user_ins,'' kode_kantor
    //     FROM (
    //     SELECT id_karyawan,nama_produk,harga_konversi,jumlah_konversi,
    //            CASE WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN harga_konversi * 0.01 
    //           WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN harga_konversi * 0.02
    //           WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 7 AND 9 THEN harga_konversi * 0.03
    //           WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 10 AND 12 THEN harga_konversi * 0.04
    //           WHEN harga_konversi <= 1000000 AND jumlah_konversi > 12 THEN harga_konversi * 0.05
    //           WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN harga_konversi * 0.02
    //           WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN harga_konversi * 0.03
    //           WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 7 AND 9 THEN harga_konversi * 0.04
    //           WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi > 9 THEN harga_konversi * 0.05
    //           WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN harga_konversi * 0.03
    //           WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN harga_konversi * 0.04
    //           WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi > 6 THEN harga_konversi * 0.05
    //           WHEN harga_konversi > 5000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN harga_konversi * 0.04
    //           WHEN harga_konversi > 5000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN harga_konversi * 0.05
    //           WHEN harga_konversi > 5000000 AND jumlah_konversi > 6 THEN harga_konversi * 0.06 END AS fee_tindakan
              
    //     FROM
    //     (
    //       SELECT id_karyawan,CASE WHEN nama_produk IN('GLAFIDSYA ACNE GLOW PEEL','GLAFIDSYA SUPER ACNE PEEL','GLAFIDSYA ANTI FLEX PEEL','GLAFIDSYA GLOWING PEEL','GLAFIDSYA PORI PEEL') THEN 'GLAFIDSYA PEEL'
    //             WHEN nama_produk IN('FACIAL PORI PEEL','FACIAL GLOWING PEEL','FACIAL FLEX PEEL','FACIAL ANTI AGING PEEL','FACIAL ACNE PEEL') THEN 'FACIAL PEEL' 
    //             ELSE nama_produk END AS nama_produk,
    //              SUM(CASE WHEN nama_produk LIKE '%GLAFIDSYA%' THEN jumlah_konversi ELSE jumlah_konversi END) AS jumlah_konversi,
    //              harga_konversi
    //       FROM
    //       (
    //         SELECT id_karyawan,nama_produk,
    //           ROUND((harga_konversi - diskon_konversi),-3) AS harga_konversi,jumlah_konversi,nama_diskon
    //         FROM (
    //           SELECT B.id_dok AS id_karyawan,D.nama_karyawan,A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,
    //              A.kode_kantor,E.isProduk,DATEDIFF(tgl_h_penjualan,D.tgl_diterima)/365 AS masa_kerja,B.id_h_diskon,nama_diskon,
    //              CASE WHEN B.status_konversi = '*' THEN
    //                 B.jumlah * B.konversi
    //               ELSE
    //                 B.jumlah / B.konversi
    //               END AS jumlah_konversi
    //               ,CASE WHEN B.status_konversi = '*' THEN
    //                 (B.harga / 1.1) / B.konversi
    //               ELSE
    //                 B.harga % B.konversi
    //               END AS harga_konversi
    //               ,CASE WHEN B.optr_diskon = '%' THEN
    //                 ((B.harga / 1.1)/100) * B.diskon
    //               ELSE
    //                 B.diskon
    //               END AS diskon_konversi,B.optr_diskon,B.diskon 
    //           FROM tb_h_penjualan A
    //           LEFT JOIN tb_d_penjualan B
    //              ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
    //           LEFT JOIN tb_costumer C 
    //              ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
    //           LEFT JOIN tb_karyawan D
    //              ON A.id_dokter = D.id_karyawan AND A.kode_kantor = D.kode_kantor
    //           LEFT JOIN tb_produk E
    //              ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
    //           LEFT JOIN tb_h_diskon F
    //              ON F.id_h_diskon = CASE WHEN B.id_d_diskon2 = '' THEN B.id_h_diskon
    //                                   WHEN B.id_h_diskon = '' THEN A.id_h_diskon ELSE '' END 
    //                 AND B.kode_kantor = F.kode_kantor
    //           LEFT JOIN tb_gaji_pokok G
    //              ON B.id_dok = G.id_karyawan
    //           WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
    //           AND B.id_dok <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
    //           AND E.isProduk IN('JASA')
    //           AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD')
    //           AND rumus_gaji = '2'
    //         ) A 
    //       ) A GROUP BY id_karyawan,CASE WHEN nama_produk IN('GLAFIDSYA ACNE GLOW PEEL','GLAFIDSYA SUPER ACNE PEEL','GLAFIDSYA ANTI FLEX PEEL','GLAFIDSYA GLOWING PEEL','GLAFIDSYA PORI PEEL') THEN 'GLAFIDSYA PEEL'
    //             WHEN nama_produk IN('FACIAL PORI PEEL','FACIAL GLOWING PEEL','FACIAL FLEX PEEL','FACIAL ANTI AGING PEEL','FACIAL ACNE PEEL') THEN 'FACIAL PEEL' 
    //             ELSE nama_produk END,harga_konversi
    //     ) A
    //     ) A GROUP BY id_karyawan
    //   ");
    // }

    // function detail_ins_dokter_middle($id_karyawan,$tgl_from,$tgl_to)
    // {
    //   $data = $this->db->query("
    //         SELECT id_karyawan,nama_produk,harga_konversi,jumlah_konversi,COALESCE(nama_diskon,'') AS nama_diskon,
    //           CASE WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN 1 
    //             WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN 2
    //             WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 7 AND 9 THEN 3
    //             WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 10 AND 12 THEN 4
    //             WHEN harga_konversi <= 1000000 AND jumlah_konversi > 12 THEN 5
    //             WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN 2
    //             WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN 3
    //             WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 7 AND 9 THEN 4
    //             WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi > 9 THEN 5
    //             WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN 3
    //             WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN 4
    //             WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi > 6 THEN 5
    //             WHEN harga_konversi > 5000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN 4
    //             WHEN harga_konversi > 5000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN 5
    //             WHEN harga_konversi > 5000000 AND jumlah_konversi > 6 THEN 6 END AS fee,
    //           CASE WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN harga_konversi * jumlah_konversi * 0.01 
    //             WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN harga_konversi * jumlah_konversi * 0.02
    //             WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 7 AND 9 THEN harga_konversi * jumlah_konversi * 0.03
    //             WHEN harga_konversi <= 1000000 AND jumlah_konversi BETWEEN 10 AND 12 THEN harga_konversi * jumlah_konversi * 0.04
    //             WHEN harga_konversi <= 1000000 AND jumlah_konversi > 12 THEN harga_konversi * jumlah_konversi * 0.05
    //             WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN harga_konversi * jumlah_konversi * 0.02
    //             WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN harga_konversi * jumlah_konversi * 0.03
    //             WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi BETWEEN 7 AND 9 THEN harga_konversi * jumlah_konversi * 0.04
    //             WHEN harga_konversi BETWEEN 1000001 AND 3000000 AND jumlah_konversi > 9 THEN harga_konversi * jumlah_konversi * 0.05
    //             WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN harga_konversi * jumlah_konversi * 0.03
    //             WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN harga_konversi * jumlah_konversi * 0.04
    //             WHEN harga_konversi BETWEEN 3000001 AND 5000000 AND jumlah_konversi > 6 THEN harga_konversi * jumlah_konversi * 0.05
    //             WHEN harga_konversi > 5000000 AND jumlah_konversi BETWEEN 1 AND 3 THEN harga_konversi * jumlah_konversi * 0.04
    //             WHEN harga_konversi > 5000000 AND jumlah_konversi BETWEEN 4 AND 6 THEN harga_konversi * jumlah_konversi * 0.05
    //             WHEN harga_konversi > 5000000 AND jumlah_konversi > 6 THEN harga_konversi * jumlah_konversi * 0.06 END AS fee_tindakan
    //           FROM
    //           (
    //             SELECT id_karyawan,CASE WHEN nama_produk IN('GLAFIDSYA ACNE GLOW PEEL','GLAFIDSYA SUPER ACNE PEEL','GLAFIDSYA ANTI FLEX PEEL','GLAFIDSYA GLOWING PEEL','GLAFIDSYA PORI PEEL') THEN 'GLAFIDSYA PEEL'
    //             WHEN nama_produk IN('FACIAL PORI PEEL','FACIAL GLOWING PEEL','FACIAL FLEX PEEL','FACIAL ANTI AGING PEEL','FACIAL ACNE PEEL') THEN 'FACIAL PEEL' 
    //             ELSE nama_produk END AS nama_produk,
    //              SUM(CASE WHEN nama_produk LIKE '%GLAFIDSYA%' THEN jumlah_konversi ELSE jumlah_konversi END) AS jumlah_konversi,nama_diskon,
    //              harga_konversi
    //             FROM
    //             (
    //               SELECT id_karyawan,nama_produk,
    //           CASE WHEN LOCATE('BUY 1 GET 1',nama_produk) > 0 OR diskon = 100 THEN 
    //             ROUND((harga_konversi),-3)
    //                ELSE 
    //             ROUND((harga_konversi - diskon_konversi),-3)
    //           END AS harga_konversi,jumlah_konversi,nama_diskon
    //               FROM (
    //           SELECT B.id_dok AS id_karyawan,A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,
    //              A.kode_kantor,E.isProduk,B.id_h_diskon,nama_diskon,
    //              CASE WHEN B.status_konversi = '*' THEN
    //                 B.jumlah * B.konversi
    //               ELSE
    //                 B.jumlah / B.konversi
    //               END AS jumlah_konversi
    //               ,CASE WHEN nama_produk IN('FACIAL ACNE PEEL','FACIAL FLEX PEEL','FACIAL GLOWING PEEL') THEN 700000
    //                     WHEN nama_produk LIKE '%FACIAL SUPER REJUVE%' THEN 401000
    //                ELSE 
    //               CASE WHEN B.status_konversi = '*' THEN (B.harga / 1.1) / B.konversi ELSE B.harga % B.konversi END
    //               END AS harga_konversi
    //               ,CASE WHEN B.optr_diskon = '%' THEN
    //                 ((B.harga / 1.1)/100) * B.diskon
    //               ELSE
    //                 B.diskon
    //               END AS diskon_konversi,B.optr_diskon,B.diskon 
    //           FROM tb_h_penjualan A
    //           LEFT JOIN tb_d_penjualan B
    //              ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
    //           LEFT JOIN tb_costumer C 
    //              ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
    //           LEFT JOIN tb_produk E
    //              ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
    //           LEFT JOIN tb_h_diskon F
    //              ON F.id_h_diskon = CASE WHEN B.id_d_diskon2 = '' THEN B.id_h_diskon
    //                                   WHEN B.id_h_diskon = '' THEN A.id_h_diskon ELSE '' END 
    //                 AND B.kode_kantor = F.kode_kantor
    //           WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
    //           AND B.id_dok <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
    //           AND E.isProduk IN('JASA') AND B.id_dok = '".$id_karyawan."'
    //           AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD')
    //               ) A 
    //             ) A GROUP BY id_karyawan,CASE WHEN nama_produk IN('GLAFIDSYA ACNE GLOW PEEL','GLAFIDSYA SUPER ACNE PEEL','GLAFIDSYA ANTI FLEX PEEL','GLAFIDSYA GLOWING PEEL','GLAFIDSYA PORI PEEL') THEN 'GLAFIDSYA PEEL'
    //             WHEN nama_produk IN('FACIAL PORI PEEL','FACIAL GLOWING PEEL','FACIAL FLEX PEEL','FACIAL ANTI AGING PEEL','FACIAL ACNE PEEL') THEN 'FACIAL PEEL' 
    //             ELSE nama_produk END,harga_konversi,nama_diskon
    //           ) A
    //           ORDER BY nama_produk,nama_diskon
    //   ")->result();

    //   return $data;
    // }

    // function hitung_ins_dokter_lama($tgl_from,$tgl_to,$periode)
    // {
    //   $data = $this->db->query("
    //       INSERT INTO tb_d_tampung_gaji (
    //             id_karyawan,
    //             periode,
    //             kode_akun,
    //             nama_akun,
    //             jenis_akun,
    //             keterangan,
    //             nominal,
    //             user_ins,
    //             kode_kantor
    //           )
    //       SELECT A.id_dokter,'".$periode."' AS periode,'TINDAKAN' AS kode,'Insentif Tindakan' nama_akun,'DEBIT' AS jenis,'' AS keterangan, 
    //         SUM(fee_nominal) AS fee_nominal,'".$this->session->userdata('ses_id_karyawan')."' AS user_ins,'' kode_kantor
    //       FROM (
    //         SELECT id_dokter,nama_karyawan,no_faktur,nama_costumer,tgl_h_penjualan,nama_produk,nama_terapis,
    //                kode_kantor,isProduk,masa_kerja,id_h_diskon,COALESCE(nama_diskon,'') nama_diskon,jumlah_konversi,harga_konversi,optr_diskon,diskon,
    //                subtotal,
    //                CASE WHEN masa_kerja < 2 THEN 
    //               CASE WHEN LOCATE('INFUS',nama_produk) > 0 THEN 5 ELSE 3 END
    //                ELSE 
    //               CASE WHEN LOCATE('INFUS',nama_produk) > 0 OR LOCATE('THREADLIFT',nama_produk) > 0 OR LOCATE('BOTOX',nama_produk) > 0 OR LOCATE('FILLER',nama_produk) > 0 
    //              THEN 6 ELSE 5 END
    //           END AS fee_tindakan,
    //                CASE WHEN masa_kerja < 2 THEN 
    //               CASE WHEN LOCATE('INFUS',nama_produk) > 0 THEN (5 * subtotal) / 100 ELSE (3 * subtotal) / 100 END
    //                ELSE 
    //               CASE WHEN LOCATE('INFUS',nama_produk) > 0 OR LOCATE('THREADLIFT',nama_produk) > 0 OR LOCATE('BOTOX',nama_produk) > 0 OR LOCATE('FILLER',nama_produk) > 0 
    //             THEN (6 * subtotal) / 100 ELSE (5 * subtotal) / 100 END
    //           END AS fee_nominal
    //         FROM (
    //           SELECT id_dokter,nama_karyawan,no_faktur,nama_costumer,tgl_h_penjualan,nama_produk,nama_terapis,
    //            kode_kantor,isProduk,masa_kerja,id_h_diskon,nama_diskon,jumlah_konversi,harga_konversi,optr_diskon,diskon,
    //            CASE WHEN LOCATE('BUY 1 GET 1',nama_diskon) AND diskon = 100 THEN (jumlah_konversi * harga_konversi) / 2
    //           ELSE jumlah_konversi * (harga_konversi - diskon_konversi) END AS subtotal
    //           FROM (  
    //             SELECT B.id_dok AS id_dokter,D.nama_karyawan,T.nama_karyawan AS nama_terapis,A.no_faktur,A.nama_costumer AS nama_costumer,tgl_h_penjualan,E.nama_produk,
    //            A.kode_kantor,E.isProduk,DATEDIFF(tgl_h_penjualan,D.tgl_diterima)/365 AS masa_kerja,B.id_h_diskon,nama_diskon,
    //            CASE WHEN B.status_konversi = '*' THEN
    //               B.jumlah * B.konversi
    //             ELSE
    //               B.jumlah / B.konversi
    //             END AS jumlah_konversi
                
    //             ,CASE WHEN B.status_konversi = '*' THEN
    //               (B.harga / 1.1) / B.konversi
    //             ELSE
    //               B.harga % B.konversi
    //             END AS harga_konversi
                
    //             ,CASE WHEN B.optr_diskon = '%' THEN
    //               ((B.harga / 1.1)/100) * B.diskon
    //             ELSE
    //               B.diskon
    //             END AS diskon_konversi,B.optr_diskon,B.diskon
    //             FROM tb_h_penjualan A
    //             LEFT JOIN tb_d_penjualan B
    //               ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
    //             LEFT JOIN tb_costumer C 
    //               ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
    //             LEFT JOIN tb_karyawan D
    //               ON B.id_dok = D.id_karyawan AND A.kode_kantor = D.kode_kantor
    //             LEFT JOIN tb_karyawan T
    //               ON B.id_ass = T.id_karyawan AND B.kode_kantor = T.kode_kantor
    //             LEFT JOIN tb_produk E
    //               ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
    //             LEFT JOIN tb_h_diskon F
    //               ON F.id_h_diskon = CASE WHEN B.id_d_diskon2 = '' THEN B.id_h_diskon
    //                                   WHEN B.id_h_diskon = '' THEN A.id_h_diskon ELSE '' END 
    //                 AND B.kode_kantor = F.kode_kantor
    //             LEFT JOIN tb_gaji_pokok G
    //               ON B.id_dok = G.id_karyawan
    //             WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
    //               AND B.id_dok <> '' AND type_h_penjualan IN('KONSULTASI KECANTIKAN','PENJUALAN LANGSUNG')
    //               AND sts_penjualan IN ('SELESAI','PEMBAYARAN') AND E.jenis_tindakan IN('DOKTER','GABUNGAN')
    //               AND E.isProduk IN('JASA') 
    //               AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD')
    //               AND rumus_gaji = '3'
    //           ) A
    //         ) A ORDER BY tgl_h_penjualan,nama_costumer
    //       ) A GROUP BY id_dokter
    //   ");
    // }

    function hitung_total_tindakan_dokter($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
          SELECT COUNT(nama_produk) AS total_tindakan,5000 AS fee,COUNT(nama_produk) * 5000 AS fee_total
          FROM tb_h_penjualan A
          LEFT JOIN tb_d_penjualan B
             ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
          LEFT JOIN tb_produk E
             ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
          WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
          AND B.id_dok <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
          AND E.isProduk IN('JASA') AND B.id_dok = '".$id_karyawan."'
          AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD')
          AND B.id_d_diskon2 = '' AND A.id_h_diskon = '' AND B.diskon = 0
      ")->row();

      return $data; 
    }

    function cek_tindakan_bft($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
          SELECT COUNT(nama_produk) AS total,GROUP_CONCAT(nama_produk,', ')  nama_produk
          FROM tb_h_penjualan A
          LEFT JOIN tb_d_penjualan B
             ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
          LEFT JOIN tb_produk E
             ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
          WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
          AND B.id_dok <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') AND E.jenis_tindakan IN ('DOKTER','GABUNGAN')
          AND E.isProduk IN('JASA') AND B.id_dok = '".$id_karyawan."'
          AND E.nama_produk NOT IN('ADM KONSUL','BIAYA ADM','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD')
          AND (LOCATE('THREADLIFT',nama_produk) > 0 OR LOCATE('BOTOX',nama_produk) > 0 OR LOCATE('FILLER',nama_produk) > 0)
      ")->row();

      return $data; 
    }

    function hitung_ins_ass_dokter($tgl_from,$tgl_to,$periode)
    {
      $data = $this->db->query("
             INSERT INTO tb_d_tampung_gaji (
                id_karyawan,
                periode,
                kode_akun,
                nama_akun,
                jenis_akun,
                keterangan,
                nominal,
                user_ins,
                kode_kantor
              )
              SELECT id_ass,'".$periode."' AS periode,'ASSIST' AS kode,'Insentif Asst Dokter' nama_akun,'DEBIT' AS jenis,'' AS keterangan, 
               SUM(2000) AS fee_terapis,'".$this->session->userdata('ses_id_karyawan')."' AS user_ins,'' kode_kantor
              FROM tb_h_penjualan A
              LEFT JOIN tb_d_penjualan B
                 ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
              LEFT JOIN tb_produk E
                 ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
              LEFT JOIN tb_karyawan K
                 ON B.id_ass = K.id_karyawan AND B.kode_kantor = K.kode_kantor
              LEFT JOIN tb_karyawan_jabatan J
                 ON K.id_karyawan = J.id_karyawan AND K.kode_kantor = J.kode_kantor
              LEFT JOIN tb_jabatan T
                 ON J.id_jabatan = T.id_jabatan AND J.kode_kantor = T.kode_kantor
              WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
              AND id_ass <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') 
              AND id_dok <> '' AND jenis_tindakan = 'DOKTER'
              AND (LOCATE('INFUS',nama_produk) = 0 OR LOCATE('INJECT',nama_produk) = 0 AND LOCATE('INJECT ACNE',nama_produk) > 0 )
              AND E.isProduk IN('JASA')
              AND E.nama_produk NOT IN('ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','ONGKIR')
              GROUP BY id_ass
      ");
    }

    
    function hitung_ins_terapis($tgl_from,$tgl_to,$periode)
    {
      $data = $this->db->query("
        INSERT INTO tb_d_tampung_gaji (
            id_karyawan,
            periode,
            kode_akun,
            nama_akun,
            jenis_akun,
            keterangan,
            nominal,
            user_ins,
            kode_kantor
          )
          SELECT A.id_karyawan,'".$periode."' AS periode,'TINDAKAN' AS kode,'Insentif Tindakan' nama_akun,'DEBIT' AS jenis,'' AS keterangan, 
             SUM(fee_terapis) AS fee_terapis,'".$this->session->userdata('ses_id_karyawan')."' AS user_ins,'' kode_kantor
          FROM
          (
            SELECT id_ass AS id_karyawan,
                   CASE WHEN LOCATE('BUY 1 GET 1',nama_produk) > 0 OR B.diskon = 100 THEN B.harga * 0.005 ELSE B.harga * 0.01 END AS fee_terapis
            FROM tb_h_penjualan A
            LEFT JOIN tb_d_penjualan B
               ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
            LEFT JOIN tb_produk E
               ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
            LEFT JOIN tb_h_diskon D
               ON A.id_h_diskon = D.id_h_diskon AND A.kode_kantor = D.kode_kantor
            LEFT JOIN tb_karyawan K
               ON B.id_ass = K.id_karyawan AND B.kode_kantor = K.kode_kantor
            LEFT JOIN tb_karyawan_jabatan J
               ON K.id_karyawan = J.id_karyawan AND K.kode_kantor = J.kode_kantor
            LEFT JOIN tb_jabatan T
               ON J.id_jabatan = T.id_jabatan AND J.kode_kantor = T.kode_kantor
            WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
            AND id_ass <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') 
            -- AND B.id_ass = 'ONJKT2020060200004' 
            AND E.isProduk IN('JASA') AND nama_jabatan IN('PERAWAT','DOKTER')
            AND (LOCATE('INFUS',nama_produk) > 0 OR LOCATE('INJECT',nama_produk) > 0 AND LOCATE('INJECT ACNE',nama_produk) = 0 )
            AND E.nama_produk NOT IN('ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','ONGKIR')


            UNION ALL

            SELECT id_ass AS id_karyawan,2000 AS fee_terapis
            FROM tb_h_penjualan A
            LEFT JOIN tb_d_penjualan B
               ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
            LEFT JOIN tb_produk E
               ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
            WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
            AND id_ass <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') 
            AND E.isProduk IN('JASA')
            AND (LOCATE('DERMA',nama_produk) > 0 OR LOCATE('PRP',nama_produk) > 0)
            AND E.nama_produk NOT IN('ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','ONGKIR')
            
            UNION ALL
            
            SELECT id_ass AS id_karyawan,
                   CASE WHEN nama_produk LIKE '%BODY SPA%' THEN 10000 
                  WHEN nama_produk LIKE '%EYELASH%' THEN 15000 ELSE 0 END AS fee_terapis
            FROM tb_h_penjualan A
            LEFT JOIN tb_d_penjualan B
               ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
            LEFT JOIN tb_produk E
               ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
            WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
            AND id_ass <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') 
            -- AND B.id_ass = 'ONJKT2020060200004' 
            AND E.isProduk IN('JASA') 
            AND (LOCATE('BODY SPA',nama_produk) > 0 OR LOCATE('EYELASH',nama_produk) > 0)
            AND E.nama_produk NOT IN('ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','ONGKIR')

            UNION ALL
    
            SELECT id_ass AS id_karyawan,
              CASE WHEN COUNT(A.id_h_penjualan) >= 125 THEN COUNT(A.id_h_penjualan) * 3000 ELSE 0 END AS fee
            FROM tb_h_penjualan A
            LEFT JOIN tb_d_penjualan B
              ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
            LEFT JOIN tb_produk E
              ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
            WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
                AND id_ass <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') 
                -- AND B.id_ass = 'ONJKT2020060200004' 
                AND E.isProduk IN('JASA') 
                AND E.nama_produk NOT IN('ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','ONGKIR')
            GROUP BY id_ass
          ) A GROUP BY id_karyawan
      "); 
    }

    function detail_ins_terapis($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
        SELECT DISTINCT  tgl_h_penjualan,nama_costumer,nama_diskon,diskon,infus_inject,jumlah_insfus,ass_dokter,jumlah_ass,tindakan_125,jumlah_tindakan,fee_terapis
        FROM
        (
          SELECT tgl_h_penjualan,nama_costumer,nama_diskon,diskon,
                 CASE WHEN divi = 'INFUS INJECT' THEN nama_produk ELSE '' END AS infus_inject,
                 CASE WHEN divi = 'INFUS INJECT' THEN jumlah ELSE '' END AS jumlah_insfus,
                 CASE WHEN divi = 'ass dokter' THEN nama_produk ELSE '' END AS ass_dokter,
                 CASE WHEN divi = 'ass dokter' THEN jumlah ELSE '' END AS jumlah_ass,fee_terapis,
                 CASE WHEN divi = 'Tindak > 125' THEN 'Tindakan > 125' ELSE '' END AS tindakan_125,
                 CASE WHEN divi = 'Tindak > 125' THEN jumlah ELSE '' END AS jumlah_tindakan
          FROM
          (
            SELECT 'INFUS INJECT' AS divi, tgl_h_penjualan,nama_costumer,nama_produk,COALESCE(nama_diskon,'') nama_diskon,
              B.diskon,B.jumlah,CASE WHEN LOCATE('BUY 1 GET 1',nama_produk) > 0 OR B.diskon = 100 THEN B.harga * 0.005 
                         WHEN B.diskon <> 100 AND B.diskon > 0 THEN B.harga * ((1 - (B.diskon/100))/100)
              ELSE B.harga * 0.01 END AS fee_terapis
            FROM tb_h_penjualan A
            LEFT JOIN tb_d_penjualan B
               ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
            LEFT JOIN tb_produk E
               ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
            LEFT JOIN tb_h_diskon D
               ON A.id_h_diskon = D.id_h_diskon AND A.kode_kantor = D.kode_kantor
            LEFT JOIN tb_karyawan K
               ON B.id_ass = K.id_karyawan AND B.kode_kantor = K.kode_kantor
            LEFT JOIN tb_karyawan_jabatan J
               ON K.id_karyawan = J.id_karyawan AND K.kode_kantor = J.kode_kantor
            LEFT JOIN tb_jabatan T
               ON J.id_jabatan = T.id_jabatan AND J.kode_kantor = T.kode_kantor
            WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
            AND id_ass <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') 
            AND B.id_ass = '".$id_karyawan."' AND nama_jabatan IN('DOKTER','DOKTER')
            AND E.isProduk IN('JASA')
            AND (LOCATE('INFUS',nama_produk) > 0 OR LOCATE('INJECT',nama_produk) > 0 AND LOCATE('INJECT ACNE',nama_produk) = 0 )
            AND E.nama_produk NOT IN('ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','ONGKIR')

            UNION ALL

            SELECT 'ass dokter' AS divi, tgl_h_penjualan,nama_costumer,nama_produk,'' nama_diskon,'' diskon,B.jumlah,2000 AS fee_terapis
            FROM tb_h_penjualan A
            LEFT JOIN tb_d_penjualan B
               ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
            LEFT JOIN tb_produk E
               ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
            WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
            AND id_ass <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') 
            AND id_dok <> '' AND jenis_tindakan = 'DOKTER' 
            AND (LOCATE('INFUS',nama_produk) = 0 OR LOCATE('INJECT',nama_produk) = 0 AND LOCATE('INJECT ACNE',nama_produk) > 0 )
            AND B.id_ass = '".$id_karyawan."' 
            AND E.isProduk IN('JASA')
            AND E.nama_produk NOT IN('ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','ONGKIR')

            UNION ALL

            SELECT 'derma prp' AS divi, tgl_h_penjualan,nama_costumer,nama_produk,'' nama_diskon,'' diskon,B.jumlah,2000 AS fee_terapis
            FROM tb_h_penjualan A
            LEFT JOIN tb_d_penjualan B
               ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
            LEFT JOIN tb_produk E
               ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
            WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
            AND id_ass <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') 
            AND B.id_ass = '".$id_karyawan."' 
            AND E.isProduk IN('JASA')
            AND (LOCATE('DERMA',nama_produk) > 0 OR LOCATE('PRP',nama_produk) > 0)
            AND E.nama_produk NOT IN('ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','ONGKIR')
            

            UNION ALL
              
            SELECT 'body spa eyelash' AS divi, tgl_h_penjualan,nama_costumer,nama_produk,'' nama_diskon,'' diskon,B.jumlah,
              CASE WHEN nama_produk LIKE '%BODY SPA%' THEN 10000 
                   WHEN nama_produk LIKE '%EYELASH%' THEN 15000 ELSE 0 END AS fee_terapis
            FROM tb_h_penjualan A
            LEFT JOIN tb_d_penjualan B
               ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
            LEFT JOIN tb_produk E
               ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
            WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
            AND id_ass <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') 
            AND B.id_ass = '".$id_karyawan."' 
            AND E.isProduk IN('JASA')
            AND (LOCATE('BODY SPA',nama_produk) > 0 OR LOCATE('EYELASH',nama_produk) > 0)
            AND E.nama_produk NOT IN('ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','ONGKIR')

            UNION ALL
            
            SELECT * FROM (
              SELECT 'Tindak > 125' AS divi,'' tgl_h_penjualan,'' nama_costumer,'' nama_produk,'' nama_diskon,'' diskon,COUNT(A.id_h_penjualan) jumlah,
                     COUNT(A.id_h_penjualan) * 3000 as fee
              FROM tb_h_penjualan A
              LEFT JOIN tb_d_penjualan B
                ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
              LEFT JOIN tb_produk E
                ON B.id_produk = E.id_produk AND B.kode_kantor = E.kode_kantor
              WHERE A.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
                  AND id_ass <> '' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') 
                  AND B.id_ass = '".$id_karyawan."' 
                  AND E.isProduk IN('JASA')
                  AND E.nama_produk NOT IN('ADM KONSUL','BIAYA PENDAFTARAN','ADM KONSUL DR REZA','ADM MEMBER REGULER','ADM MEMBER GOLD','BIAYA ADM','ONGKIR')
            ) A WHERE jumlah >= 125
          ) A ORDER BY tgl_h_penjualan
        ) A ORDER BY tgl_h_penjualan
      ")->result();

      return $data;
    }

    function detail_upselling($id_karyawan,$tgl_from,$tgl_to)
    {
      $data = $this->db->query("
         SELECT A.id_karyawan,A.kode_kantor,CASE WHEN B.isProduk = 'JASA' THEN
               CASE WHEN tgl_h_penjualan IS NOT NULL THEN E.harga * 0.01
               ELSE C.harga * 0.01 
               END
            ELSE 5000 END AS fee_upselling,
           CASE WHEN B.isProduk = 'JASA' THEN
             CASE WHEN tgl_h_penjualan IS NOT NULL THEN E.harga ELSE C.harga
           END ELSE C.harga END AS harga,COALESCE(nama_diskon,'') AS nama_diskon,
            P.id_produk,P.qty,B.nama_produk,B.kode_produk,tanggal
          FROM tb_h_upselling A
          LEFT JOIN tb_d_upselling P
            ON A.id_h_upselling = P.id_h_upselling
          LEFT JOIN tb_produk B
            ON B.id_produk = P.id_produk AND B.kode_kantor = 'JKT'
          LEFT JOIN tb_produk_harga_satuan_costumer C
            ON P.id_produk = C.id_produk AND C.kode_kantor = 'JKT' AND C.media = 'KLINIK' AND C.id_costumer = 'ONJKT2020060200002'
          LEFT JOIN
          (
             SELECT tgl_h_penjualan,id_produk,(A.harga - (A.harga * A.diskon / 100)) * A.jumlah AS harga,A.kode_kantor,nama_diskon
             FROM tb_d_penjualan A
             LEFT JOIN tb_h_penjualan B
              ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
             LEFT JOIN tb_h_diskon C
              ON A.id_h_diskon = C.id_h_diskon AND A.kode_kantor = C.kode_kantor
             WHERE B.tgl_h_penjualan BETWEEN '".$tgl_from."' AND '".$tgl_to."'
             AND A.id_h_diskon <> '' AND isProduk = 'JASA' 
          ) E ON A.tanggal = E.tgl_h_penjualan AND P.id_produk = E.id_produk AND A.kode_kantor = E.kode_kantor
        WHERE tanggal BETWEEN '".$tgl_from."' AND '".$tgl_to."' AND B.isProduk IN('JASA','PRODUK') AND COALESCE(foto_bukti,'') <> '' AND id_karyawan = '".$id_karyawan."'
      ")->result();

      return $data;
    }

    function list_kantor()
    {
      $data = $this->db->query("
          SELECT * FROM tb_kantor
        ")->result();
      return $data;
    }

    function cek_d_tampung($id_karyawan,$periode,$kode_akun)
    {
        $data = $this->db->query("
            SELECT * FROM tb_d_tampung_gaji
            WHERE id_karyawan = '".$id_karyawan."' AND periode = '".$periode."' AND kode_akun = '".$kode_akun."'
         ")->result();

        return $data;
    }

    function update_d_tampung($id_karyawan,$periode,$kode_akun,$nominal)
    {
        $this->db->query("
            UPDATE tb_d_tampung_gaji
            SET nominal = nominal + ".$nominal."
            WHERE id_karyawan = '".$id_karyawan."' AND periode = '".$periode."' AND kode_akun = '".$kode_akun."'
         ");
    }

    function update_d_tampung2($id_karyawan,$periode,$kode_akun,$nominal)
    {
        $this->db->query("
            UPDATE tb_d_tampung_gaji
            SET nominal = ".$nominal."
            WHERE id_karyawan = '".$id_karyawan."' AND periode = '".$periode."' AND kode_akun = '".$kode_akun."'
         ");
    }

    function update_d_tampung_by_id($id_tampung,$nominal)
    {
        $this->db->query("
            UPDATE tb_d_tampung_gaji
            SET nominal = ".$nominal."
            WHERE id_tampung = '".$id_tampung."'
         ");
    }

    function insert_proses_tunjangan($periode)
    {
      $this->db->query("
          INSERT INTO tb_d_payment (
            id_karyawan,
            periode,
            kode_akun,
            nama_akun,
            jenis_akun,
            keterangan,
            nominal,
            user_ins,
            kode_kantor
          )

          SELECT A.id_karyawan,'".$periode."' AS periode,COALESCE(B.kode_tunjangan,'') AS kode_akun,B.nama_tunjangan,
           'DEBIT' AS jenis_akun,'' Keterangan,
           CASE WHEN B.kode_tunjangan NOT IN('KONSUL1','KONSUL2','LEMBUR','TINDAKAN','UPSELLING','UM','LIBUR','KENAIKAN','ASSIST')
            THEN COALESCE(C.nominal,A.nominal) ELSE COALESCE(C.nominal,0) END AS nominal,
           '".$this->session->userdata('ses_id_karyawan')."' user_ins,A.kode_kantor
          FROM tb_tunjangan_karyawan A
          LEFT JOIN tb_tunjangan B
            ON A.id_tunjangan = B.id_tunjangan
          LEFT JOIN tb_d_tampung_gaji C
            ON B.kode_tunjangan = C.kode_akun AND A.id_karyawan = C.id_karyawan 
               AND C.periode = '".$periode."'

      ");

      /*
        SELECT A.id_karyawan,'".$periode."' AS periode,COALESCE(kode_tunjangan,'') AS kode_akun,nama_tunjangan,
                 'DEBIT' AS jenis_akun,'' Keterangan,A.nominal,'".$this->session->userdata('ses_id_karyawan')."' user_ins,A.kode_kantor
          FROM tb_tunjangan_karyawan A
          LEFT JOIN tb_tunjangan B
            ON A.id_tunjangan = B.id_tunjangan
          LEFT JOIN tb_karyawan C
            ON A.id_karyawan = C.id_karyawan AND A.kode_kantor = C.kode_kantor
          WHERE A.is_aktif = '0' AND C.isDefault = '1' AND kode_tunjangan NOT IN('LATE','ABSEN','KONSUL1','KONSUL2','LEMBUR','TINDAKAN','UPSELLING','UM','LIBUR','LEBIH10','LEBIH1','KENAIKAN')




          SELECT
            id_karyawan,
            periode,
            kode_akun,
            nama_akun,
            jenis_akun,
            keterangan,
            nominal,
            user_ins,
            kode_kantor
          FROM tb_d_tampung_gaji
          WHERE periode = '".$periode."' AND jenis_akun = 'DEBIT'

          UNION ALL

          SELECT A.id_karyawan,'".$periode."' AS periode,COALESCE(kode_tunjangan,'') AS kode_akun,nama_tunjangan,
                 'DEBIT' AS jenis_akun,'' Keterangan,A.nominal,'".$this->session->userdata('ses_id_karyawan')."' user_ins,A.kode_kantor
          FROM tb_tunjangan_karyawan A
          LEFT JOIN tb_tunjangan B
            ON A.id_tunjangan = B.id_tunjangan
          LEFT JOIN tb_karyawan C
            ON A.id_karyawan = C.id_karyawan AND A.kode_kantor = C.kode_kantor
          WHERE A.is_aktif = '0' AND kode_tunjangan NOT IN('LATE','ABSEN','KONSUL1','KONSUL2','LEMBUR','TINDAKAN','UPSELLING','UM','LIBUR','LEBIH10','LEBIH1','KENAIKAN')
      */
    }

    function insert_proses_potongan($periode)
    {
      $this->db->query("
          INSERT INTO tb_d_payment (
            id_karyawan,
            periode,
            kode_akun,
            nama_akun,
            jenis_akun,
            keterangan,
            nominal,
            user_ins,
            kode_kantor
          )
          SELECT A.id_karyawan,'".$periode."' AS periode,COALESCE(B.kode_potongan,'') AS kode_akun,B.nama_potongan,
           'KREDIT' AS jenis_akun,'' Keterangan,
           CASE WHEN B.kode_potongan NOT IN('LATE','ABSEN','PUNISH') THEN COALESCE(C.nominal,A.nominal) ELSE COALESCE(C.nominal,0) END AS nominal,
           '".$this->session->userdata('ses_id_karyawan')."' user_ins,A.kode_kantor
          FROM tb_potongan_karyawan A
          LEFT JOIN tb_potongan B
            ON A.id_potongan = B.id_potongan
          LEFT JOIN tb_d_tampung_gaji C
            ON B.kode_potongan = C.kode_akun AND A.id_karyawan = C.id_karyawan -- AND A.kode_kantor = C.kode_kantor
               AND C.periode = '".$periode."'
      ");

      /*
        SELECT A.id_karyawan,'".$periode."' AS periode,COALESCE(kode_potongan,'') AS kode_akun,nama_potongan,
                 'KREDIT' AS jenis_akun,'' Keterangan,A.nominal,'".$this->session->userdata('ses_id_karyawan')."' user_ins,A.kode_kantor
          FROM tb_potongan_karyawan A
          LEFT JOIN tb_potongan B
            ON A.id_potongan = B.id_potongan
          LEFT JOIN tb_karyawan C
            ON A.id_karyawan = C.id_karyawan AND A.kode_kantor = C.kode_kantor
          WHERE A.is_aktif = '0' AND C.isDefault = '1'
          AND B.kode_potongan NOT IN('LATE','ABSEN','PUNISH')


           SELECT
            id_karyawan,
            periode,
            kode_akun,
            nama_akun,
            jenis_akun,
            keterangan,
            nominal,
            user_ins,
            kode_kantor
          FROM tb_d_tampung_gaji
          WHERE periode = '".$periode."' AND jenis_akun = 'KREDIT'

          UNION ALL

          SELECT A.id_karyawan,'".$periode."' AS periode,COALESCE(kode_potongan,'') AS kode_akun,nama_potongan,
                 'KREDIT' AS jenis_akun,'' Keterangan,A.nominal,'".$this->session->userdata('ses_id_karyawan')."' user_ins,A.kode_kantor
          FROM tb_potongan_karyawan A
          LEFT JOIN tb_potongan B
            ON A.id_potongan = B.id_potongan
          LEFT JOIN tb_karyawan C
            ON A.id_karyawan = C.id_karyawan AND A.kode_kantor = C.kode_kantor
          WHERE A.is_aktif = '0'
          AND B.kode_potongan NOT IN('LATE','ABSEN','PUNISH') 


      */
    }

    function insert_header_payment($data)
    {
      $this->db->insert('tb_payment',$data);
    }

    function delete_payment($periode)
    {
      $this->db->query("DELETE FROM tb_payment WHERE periode = '".$periode."'");
    }

    function laporan_gaji($periode,$cari,$kode_kantor)
    {
      $data = $this->db->query("
                                SELECT '1' AS divi,A.*,B.tgl_diterima 
                                FROM tb_payment A
                                LEFT JOIN tb_karyawan B
                                  ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
                                LEFT JOIN tb_gaji_pokok C
                                  ON A.id_karyawan = C.id_karyawan
                                WHERE periode = '".$periode."' AND B.nama_karyawan LIKE '%".$cari."%'
                                      AND A.kode_kantor LIKE '%".$kode_kantor."%'
                                      AND C.id_karyawan IS NOT NULL AND jabatan = 'DOKTER'
                                      
                                UNION ALL

                                SELECT '2' AS divi,A.*,B.tgl_diterima 
                                FROM tb_payment A
                                LEFT JOIN tb_karyawan B
                                  ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
                                LEFT JOIN tb_gaji_pokok C
                                  ON A.id_karyawan = C.id_karyawan
                                WHERE periode = '".$periode."' AND B.nama_karyawan LIKE '%".$cari."%'
                                      AND A.kode_kantor LIKE '%".$kode_kantor."%'
                                      AND C.id_karyawan IS NOT NULL AND B.isDokter = 'MANAGEMENT'
                                      
                                UNION ALL

                                SELECT '3' AS divi,A.*,B.tgl_diterima 
                                FROM tb_payment A
                                LEFT JOIN tb_karyawan B
                                  ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
                                LEFT JOIN tb_gaji_pokok C
                                  ON A.id_karyawan = C.id_karyawan
                                WHERE periode = '".$periode."' AND B.nama_karyawan LIKE '%".$cari."%'
                                      AND A.kode_kantor LIKE '%".$kode_kantor."%'
                                      AND C.id_karyawan IS NOT NULL AND jabatan <> 'DOKTER' AND B.isDokter <> 'MANAGEMENT'
                                ORDER BY divi,kode_kantor,nama_karyawan
      ")->result();

      return $data;
    }

    function laporan_gaji_dokter($periode,$cari,$kode_kantor)
    {
      $data = $this->db->query("
                                SELECT '1' AS divi,A.*,B.tgl_diterima 
                                FROM tb_payment A
                                LEFT JOIN tb_karyawan B
                                  ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
                                LEFT JOIN tb_gaji_pokok C
                                  ON A.id_karyawan = C.id_karyawan
                                WHERE periode = '".$periode."' AND B.nama_karyawan LIKE '%".$cari."%'
                                      AND C.id_karyawan IS NOT NULL AND jabatan = 'DOKTER'
      ")->result();

      return $data;
    }

    function laporan_gaji_management($periode,$cari,$kode_kantor)
    {
      $data = $this->db->query("
                                SELECT '2' AS divi,A.*,B.tgl_diterima 
                                FROM tb_payment A
                                LEFT JOIN tb_karyawan B
                                  ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
                                LEFT JOIN tb_gaji_pokok C
                                  ON A.id_karyawan = C.id_karyawan
                                WHERE periode = '".$periode."' AND B.nama_karyawan LIKE '%".$cari."%'
                                      AND C.id_karyawan IS NOT NULL AND B.isDokter = 'MANAGEMENT'
      ")->result();

      return $data;
    }

    function laporan_gaji_cabang($periode,$cari,$kode_kantor)
    {
      $data = $this->db->query("
                                SELECT '3' AS divi,A.*,B.tgl_diterima 
                                FROM tb_payment A
                                LEFT JOIN tb_karyawan B
                                  ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
                                LEFT JOIN tb_gaji_pokok C
                                  ON A.id_karyawan = C.id_karyawan
                                WHERE periode = '".$periode."' AND B.nama_karyawan LIKE '%".$cari."%'
                                      AND A.kode_kantor LIKE '%".$kode_kantor."%'
                                      AND C.id_karyawan IS NOT NULL AND jabatan <> 'DOKTER' AND B.isDokter <> 'MANAGEMENT'
                                ORDER BY divi,kode_kantor,nama_karyawan
      ")->result();

      return $data;
    }

    function get_header_payment($id)
    {
      $data = $this->db->query("
          SELECT id_payment,
                  id_karyawan,
                  nama_karyawan,
                  no_karyawan,
                  jabatan,
                  tgl_payment,
                  periode,
                  gaji_pokok,
                  total_tunjangan,
                  total_potongan,
                  pph_21,
                  gaji_kotor,
                  gaji_bersih,
                  is_approve,
                  tgl_ins,
                  tgl_updt,
                  user_ins,
                  user_updt,
                  kode_kantor 
          FROM tb_payment
          WHERE id_payment = '".$id."'
      ")->row();

      return $data;
    }

    function get_detail_payment($id_karyawan,$periode)
    {
      $data = $this->db->query("
          SELECT id_tampung,
                id_karyawan,
                periode,
                kode_akun,
                nama_akun,
                jenis_akun,
                keterangan,
                nominal,
                tgl_ins,
                tgl_updt,
                user_ins,
                user_updt,
                kode_kantor
          FROM tb_d_payment
          WHERE id_karyawan = '".$id_karyawan."' AND periode = '".$periode."'
      ")->result();

      return $data;
    }

}
