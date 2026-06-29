#!/usr/bin/env node
// convert-mechanical.mjs — konversi mekanis sintaks CI3 -> CI4 (SAFE regex only).
// Default --dry-run (print diff, tidak ubah). --apply untuk tulis file.

import { readFileSync, writeFileSync, readdirSync, statSync } from 'node:fs';
import { join, extname } from 'node:path';
import { fileURLToPath } from 'node:url';

// Urutan penting: transformasi spesifik dulu.
export const transformations = [
  { name: 'input->post',           pattern: /\$this->input->post\(/g,  replacement: '$this->request->getPost(' },
  { name: 'input->get',            pattern: /\$this->input->get\(/g,   replacement: '$this->request->getGet(' },
  { name: 'session set_userdata',  pattern: /\$this->session->set_userdata\(/g, replacement: 'session()->set(' },
  { name: 'session userdata',      pattern: /\$this->session->userdata\(/g,     replacement: 'session()->get(' },
  { name: 'session set_flashdata', pattern: /\$this->session->set_flashdata\(/g, replacement: 'session()->setFlashdata(' },
  { name: 'load->view',            pattern: /\$this->load->view\(/g,  replacement: 'view(' },
  { name: 'load->helper',          pattern: /\$this->load->helper\(/g, replacement: 'helper(' },
  { name: 'uri->segment',          pattern: /\$this->uri->segment\(/g, replacement: '$this->request->uri->getSegment(' },
];

export function transformCode(src) {
  let out = src;
  for (const t of transformations) out = out.replace(t.pattern, t.replacement);
  return out;
}

export function diffLines(src, out) {
  const a = src.split('\n'), b = out.split('\n');
  const n = Math.max(a.length, b.length);
  const changes = [];
  for (let i = 0; i < n; i++) {
    if (a[i] !== b[i]) changes.push({ line: i + 1, before: a[i] ?? '', after: b[i] ?? '' });
  }
  return changes;
}

function walkPhp(dir) {
  const out = [];
  for (const e of readdirSync(dir)) {
    const p = join(dir, e);
    if (statSync(p).isDirectory()) out.push(...walkPhp(p));
    else if (extname(p) === '.php') out.push(p);
  }
  return out;
}

function main() {
  const args = process.argv.slice(2);
  const apply = args.includes('--apply');
  const target = args.find(a => !a.startsWith('--'));
  if (!target) {
    console.error('Usage: node convert-mechanical.mjs <file|dir> [--apply]');
    process.exit(1);
  }
  const files = statSync(target).isDirectory() ? walkPhp(target) : [target];
  let total = 0;
  for (const f of files) {
    const src = readFileSync(f, 'utf8');
    const out = transformCode(src);
    if (src === out) continue;
    const changes = diffLines(src, out);
    total += changes.length;
    console.log(`\n=== ${f} (${changes.length} change${changes.length > 1 ? 's' : ''}) [${apply ? 'APPLIED' : 'DRY-RUN'}] ===`);
    for (const c of changes) {
      console.log(`  line ${c.line}:`);
      console.log(`    - ${c.before}`);
      console.log(`    + ${c.after}`);
    }
    if (apply) writeFileSync(f, out, 'utf8');
  }
  console.log(`\nTotal: ${total} change${total !== 1 ? 's' : ''} ${apply ? 'applied' : '(dry-run; --apply to write)'}.`);
}

if (process.argv[1] === fileURLToPath(import.meta.url)) main();
