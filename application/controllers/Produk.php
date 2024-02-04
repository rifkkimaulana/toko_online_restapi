<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Produk extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function list_get()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header('Content-Type: application/json');

        $search = $this->input->get('search', TRUE);
        $this->db->select('*');
        $this->db->from('produk');
        //clausa like di gunakan untuk filter pada pencarian sesuai keyword pencarian
        if ($search != '') {
            $this->db->like('nama', $search);
        }

        $this->db->order_by('nama', 'ASC');
        $produk = $this->db->get();

        if ($produk->num_rows() > 0) {
            $this->response([
                'status' => TRUE,
                'data' => $produk->result()
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'Barang tidak ditemukan'
            ], REST_Controller::HTTP_OK);
        }
    }

    public function simpan_post()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods:POST");
        header('Content-Type: application/json');

        $id = $this->input->post('id', TRUE);

        if (!empty($_FILES['image']['tmp_name'])) {
            $errors = array();
            $allowed_ext = array('jpg', 'jpeg', 'png',);
            $file_size = $_FILES['image']['size'];
            $file_tmp = $_FILES['image']['tmp_name'];
            //$type = pathinfo($file_tmp, PATHINFO_EXTENSION);
            $type = 'jpeg';
            $data = file_get_contents($file_tmp);
            $tmp = explode('.', $_FILES['image']['name']);
            $file_ext = end($tmp);

            if (in_array($file_ext, $allowed_ext) === false) {
                $errors[] = 'Ekstensi file tidak di izinkan';
                echo json_encode(['status' => false, 'message' => 'Ekstensi file tidak di izinkan']);
                die();
            }

            if ($file_size > 2097152) {
                $errors[] = 'Ukuran file maksimal 2 MB';
                echo json_encode(['status' => false, 'message' => 'Ukuran file maksimal 2 MB']);
                die();
            }

            if (empty($errors)) {
                $base64 = 'data:image/' . $type . ';base64,' .
                    base64_encode($data);
                $data = [
                    'nama' => $this->input->post('nama', TRUE),
                    'harga' => $this->input->post('harga', TRUE),
                    'deskripsi' => $this->input->post('deskripsi', TRUE),
                    'img' => $base64
                ];
            } else {
                echo json_encode($errors);
            }
        } else {
            $data = [
                'nama' => $this->input->post('nama', TRUE),
                'harga' => $this->input->post('harga', TRUE),
                'deskripsi' => $this->input->post('deskripsi', TRUE),
            ];
        }

        if ($id == "") {
            $this->db->insert('produk', $data);
            $msg = "Data barang berhasil ditambahkan";
        } else {
            $this->db->where('id', $id);
            $this->db->update('produk', $data);
            $msg = "Data barang berhasil diubah";
        }

        echo json_encode(['status' => true, 'message' => $msg]);
    }

    public function detail_get($id)
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET");
        header('Content-Type: application/json');

        $produk = $this->db->get_where('produk', ['id' => $id]);

        if ($produk->num_rows() > 0) {
            $this->response([
                'status' => TRUE,
                'data' => $produk->row()
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'Barang tidak ditemukan'
            ], REST_Controller::HTTP_OK);
        }
    }
    public function hapus_get($id)
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET");
        header('Content-Type: application/json');

        $this->db->where('id', $id);
        $this->db->delete('produk');
        echo json_encode(['status' => true, 'message' => 'Data barang berhasil dihapus']);
    }
}
