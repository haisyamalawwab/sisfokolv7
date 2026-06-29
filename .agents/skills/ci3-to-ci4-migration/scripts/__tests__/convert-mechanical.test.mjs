import { test } from 'node:test';
import assert from 'node:assert/strict';
import { transformCode, transformations } from '../convert-mechanical.mjs';

test('input->post transformed', () => {
  assert.equal(transformCode(`$x = $this->input->post('name');`), `$x = $this->request->getPost('name');`);
});
test('input->get transformed', () => {
  assert.equal(transformCode(`$x = $this->input->get('q');`), `$x = $this->request->getGet('q');`);
});
test('session set_userdata transformed', () => {
  assert.equal(transformCode(`$this->session->set_userdata('k', $v);`), `session()->set('k', $v);`);
});
test('session userdata read transformed', () => {
  assert.equal(transformCode(`$v = $this->session->userdata('k');`), `$v = session()->get('k');`);
});
test('session set_flashdata transformed', () => {
  assert.equal(transformCode(`$this->session->set_flashdata('msg', 'hi');`), `session()->setFlashdata('msg', 'hi');`);
});
test('load->view call transformed (no return prefix added)', () => {
  assert.equal(transformCode(`$this->load->view('foo', $data);`), `view('foo', $data);`);
});
test('load->helper transformed', () => {
  assert.equal(transformCode(`$this->load->helper('form');`), `helper('form');`);
});
test('uri->segment transformed', () => {
  assert.equal(transformCode(`$id = $this->uri->segment(3);`), `$id = $this->request->uri->getSegment(3);`);
});
test('multiple transformations in one snippet', () => {
  const src = [
    `$this->load->helper('form');`,
    `$x = $this->input->post('x');`,
    `$this->session->set_userdata('k', $x);`,
    `$this->load->view('v', $data);`,
  ].join('\n');
  const expected = [
    `helper('form');`,
    `$x = $this->request->getPost('x');`,
    `session()->set('k', $x);`,
    `view('v', $data);`,
  ].join('\n');
  assert.equal(transformCode(src), expected);
});
test('does NOT touch load->model (judgment, left alone)', () => {
  const src = `$this->load->model('user_model');`;
  assert.equal(transformCode(src), src);
});
test('does NOT touch extends CI_Controller', () => {
  const src = `class Foo extends CI_Controller { }`;
  assert.equal(transformCode(src), src);
});
test('does NOT touch load->library (judgment)', () => {
  const src = `$this->load->library('session');`;
  assert.equal(transformCode(src), src);
});
test('transformations list has 8 entries', () => {
  assert.equal(transformations.length, 8);
});
