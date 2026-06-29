# Mapping Table CI3 → CI4 (Quick Reference)

Lookup cepat saat debug area tertentu. Superset dari `references/`. Format: CI3 → CI4 (mekanis? / catatan).

## Controller

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `class Foo extends CI_Controller` | `namespace App\Controllers; class Foo extends BaseController` | manual (namespace+use) |
| `$this->load->model('foo_model','fm')` | `use App\Models\FooModel; $fm = new FooModel();` | manual |
| `$this->load->view('x', $data)` | `return view('x', $data)` | mekanis (call) + manual (return prefix) |
| `$this->input->post('x')` | `$this->request->getPost('x')` | mekanis |
| `$this->input->get('x')` | `$this->request->getGet('x')` | mekanis |
| `$this->input->post_get('x')` | `$this->request->getPostGet('x')` | mekanis |
| `$this->uri->segment(n)` | `$this->request->uri->getSegment(n)` | mekanis |
| `$this->output->set_content_type('json')` | `return $this->response->setJSON($d)` | manual |
| (REST) controller CI3 manual JSON | `extends ResourceController`, `$this->respond()/respondCreated()/failNotFound()` | manual |

## Model & DB

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `class Foo_model extends CI_Model` | `namespace App\Models; class FooModel extends Model` | manual |
| (file) `models/foo_model.php` | `app/Models/FooModel.php` | mekanis (rename-files.mjs) |
| `$this->db->get('t')->result()` | `$model->findAll()` / `db_connect()->table('t')->get()->getResult()` | manual |
| `$this->db->where('k',$v)->get('t')->result()` | `$model->where('k',$v)->findAll()` | manual |
| `$this->db->insert('t',$d)` | `$model->insert($d)` | manual |
| `$this->db->update('t',$d,$cond)` | `$model->update($id,$d)` / `->where()->update()` | manual |
| `$this->db->delete('t',$cond)` | `$model->delete($id)` / `->where()->delete()` | manual |
| `$query->row()` | `->first()` / `->getFirstRow()` | manual |
| `$query->result_array()` | `->getResultArray()` | manual |
| `$this->db->query($sql)` | `db_connect()->query($sql)` | manual |
| `$this->db->insert_id()` | `db_connect()->insertID()` / `$model->insertID` | manual |
| (opsional) return stdClass | `extends Entity` + `$returnType = App\Entities\Foo::class` | manual (opsional) |

## Routing

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$route['default_controller']='x'` | `$routes->setDefaultController('x')` | manual |
| `$route['foo/bar']='c/m'` | `$routes->get('foo/bar','C::m')` | sebagian (simple) |
| `$route['x/(:num)']='c/m/$1'` | `$routes->get('x/(:num)','C::m/$1')` | manual |
| `$route['404_override']='x'` | `$routes->set404Override('X::index')` | manual |
| `$route['x']['post']='c/m'` | `$routes->post('x','C::m')` | manual |

## Session

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->session->set_userdata('k',$v)` | `session()->set('k',$v)` | mekanis |
| `$this->session->userdata('k')` | `session()->get('k')` | mekanis |
| `$this->session->set_flashdata('k',$v)` | `session()->setFlashdata('k',$v)` | mekanis |
| `$this->session->flashdata('k')` | `session()->getFlashdata('k')` | manual |

## Form Validation

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->load->library('form_validation')` | (auto via service) | - |
| `$this->form_validation->set_rules('f','L','required')` | `service('validation')->setRules(['f'=>'required'],['f'=>'L'])` | manual |
| `if ($this->form_validation->run()==FALSE)` | `if (! $this->validate($rules))` | manual |

## Email / Upload / Cache / Logging

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->load->library('email'); $this->email->send()` | `$e = service('email'); $e->send()` / `email()->send()` | manual |
| `$this->load->library('upload'); $this->upload->do_upload('f')` | `$f=$this->request->getFile('f'); $f->move(WRITEPATH.'uploads')` | manual |
| `$this->load->driver('cache'); $this->cache->save('k',$v)` | `cache()->save('k',$v)` | manual |
| `log_message('error','...')` | `log_message('error','...')` (sama, global) | - |
| `$this->CI->...` di library | DI / `service()` / `Config\Services` | manual (stuck point) |

## Library / Helper

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `application/libraries/Foo.php` `class Foo` | `app/Libraries/Foo.php` `namespace App\Libraries; class Foo` | mekanis (move) + manual (namespace) |
| `$this->load->library('foo')` | `use App\Libraries\Foo; $foo = new Foo();` | manual |
| `$this->CI = &get_instance()` | DI / `service()` / `Config\Services` | manual (stuck point!) |
| `application/helpers/foo_helper.php` | `app/Helpers/foo_helper.php` | mekanis (move) |
| `$this->load->helper('foo')` | `helper('foo')` | mekanis |

## View

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->load->view('x',$data)` | `return view('x',$data)` | mekanis (call) + manual (return) |
| `$this->load->view('header');...;view('footer')` | `extend('layout') + section('content')` | manual |
| `$this->load->vars($d)` | `view('x',[...$d])` | manual |

## Config

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `config/config.php` (array) | `app/Config/App.php` (class) | manual |
| `config/database.php` (array) | `app/Config/Database.php` + `.env` | manual |
| `config/autoload.php` `$autoload` | service registration / Filters | manual |
| `$this->config->item('k')` | `config('App')->k` / `config('App')` | manual |

## Hooks → Events/Filters

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$hook['pre_controller']=...` (config/hooks.php) | `Events::on('pre_controller',...)` + `Config/Filters.php` | manual |
| `$hook['pre_system']` | `Events::on('pre_system',...)` | manual |

## Migration & Seeder

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->migration->current()/latest()` | `php spark migrate` / `php spark migrate:rollback` | manual |
| `application/migrations/001_xxx.php` | `app/Database/Migrations/001_xxx.php` | manual (move + rewrite) |
| `$this->dbforge->create_table('t')` | `$this->forge->createTable('t')` | manual |
| `$this->dbforge->add_column()/drop_table()` | `$this->forge->addColumn()/dropTable()` | manual |
| (seeder) manual | `php spark db:seed` + `app/Database/Seeds/` | manual |

## Security

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->security->xss_clean($v)` | `esc($v)` / `esc($v,'attr')` (context: html/js/css/url/attr) | manual |
| `$this->input->post('f', TRUE)` (XSS filter) | `$this->request->getPost('f')` + `esc()` saat output | manual |
| CSRF (config-based) | Filter `csrf` (default global) + `csrf_token()/csrf_hash()/csrf_field()` | manual |
| `$this->security->csrf_verify()` | (otomatis via csrf filter) | manual |

## Pagination

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->pagination->initialize($config)` | `$model->paginate(10)` + `app/Config/Pager.php` | manual |
| `$this->pagination->create_links()` | `$pager->links()` / `simpleLinks()` | manual |
