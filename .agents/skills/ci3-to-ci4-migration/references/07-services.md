# 07 — Services (Session, Form Validation, Email, Upload, Cache, Logging)

## Session

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->load->library('session')` | (auto, tidak perlu load) | - |
| `$this->session->set_userdata('k', $v)` | `session()->set('k', $v)` | **mekanis** |
| `$this->session->set_userdata(['a'=>1,'b'=>2])` | `session()->set(['a'=>1,'b'=>2])` | mekanis (sama pattern) |
| `$this->session->userdata('k')` | `session()->get('k')` | **mekanis** |
| `$this->session->set_flashdata('k', $v)` | `session()->setFlashdata('k', $v)` | **mekanis** |
| `$this->session->flashdata('k')` | `session()->getFlashdata('k')` | manual |
| `$this->session->unset_userdata('k')` | `session()->remove('k')` | manual |
| `$this->session->sess_destroy()` | `session()->destroy()` | manual |

Catatan: session CI4 perlu `session` auto-started (default via filter) — cek `app/Config/Filters.php` ada `session` di `$globals`.

## Form Validation

CI3:
```php
$this->load->library('form_validation');
$this->form_validation->set_rules('username', 'Username', 'required|min_length[3]');
if ($this->form_validation->run() == FALSE) {
    $this->load->view('form', $this->input->post());
} else {
    // save
}
```

CI4 (inline rules via controller):
```php
$rules = ['username' => 'required|min_length[3]'];
if (! $this->validate($rules)) {
    return view('form', ['validation' => $this->validator] + $this->request->getPost());
}
// save
```

Atau via config `app/Config/Validation.php` + `service('validation')->setRules(...)`.

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->form_validation->set_rules('f','L','rules')` | `$rules=['f'=>'rules']` + `$this->validate($rules)` | manual |
| `$this->form_validation->run()` | `$this->validate($rules)` (returns bool) | manual |
| `form_error('f')` (di view) | `$this->validator->getError('f')` / `validation_list_errors()` | manual |
| `set_value('f')` | `set_value('f')` (sama, helper form) | - |

## Email

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->load->library('email')` | `$email = service('email')` / `email()` | manual |
| `$this->email->from('a@b.c')` | `$email->setFrom('a@b.c')` | manual |
| `$this->email->to('x@y.z')` | `$email->setTo('x@y.z')` | manual |
| `$this->email->subject('S')` | `$email->setSubject('S')` | manual |
| `$this->email->message('M')` | `$email->setMessage('M')` | manual |
| `$this->email->send()` | `$email->send()` | manual |

## Upload

CI3:
```php
$this->load->library('upload', $config);
if (! $this->upload->do_upload('file')) {
    $error = $this->upload->display_errors();
} else {
    $data = $this->upload->data();
}
```

CI4:
```php
$file = $this->request->getFile('file');
if (! $file->isValid()) {
    $error = $file->getErrorString();
} else {
    $file->move(WRITEPATH . 'uploads');
    $name = $file->getName();
}
```

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->load->library('upload', $cfg)` | `$file = $this->request->getFile('f')` | manual |
| `$this->upload->do_upload('f')` | `$file->move(WRITEPATH.'uploads')` | manual |
| `$this->upload->data()` | `$file->getName()` / `getClientName()` / `getExtension()` | manual |
| `$this->upload->display_errors()` | `$file->getErrorString()` | manual |

## Cache

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `$this->load->driver('cache')` | `cache()` | manual |
| `$this->cache->save('k', $v, 60)` | `cache()->save('k', $v, 60)` | manual |
| `$this->cache->get('k')` | `cache()->get('k')` | manual |
| `$this->cache->delete('k')` | `cache()->delete('k')` | manual |

## Logging

| CI3 | CI4 | Mekanis? |
|-----|-----|----------|
| `log_message('error', '...')` | `log_message('error', '...')` (sama, fungsi global) | - (tidak perlu ubah) |
| `log_message('debug'/'info'/'error', $msg)` | sama, level: `debug/info/error/warning/emergency` | - |
| (butuh logger instance) | `$this->logger->debug('...')` di controller, atau `service('logger')` di library | manual |

Catatan: `log_message()` tidak berubah di CI4 — langsung jalan. Untuk logger instance (mis. di library yang butuh DI), pakai `service('logger')`. Config level/handler di `app/Config/Logger.php`.
