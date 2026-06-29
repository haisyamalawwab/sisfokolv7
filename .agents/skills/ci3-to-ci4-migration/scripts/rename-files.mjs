#!/usr/bin/env node
// rename-files.mjs — rename+pindah file CI3 (application/) ke struktur CI4 (app/).
// models/user_model.php -> app/Models/UserModel.php (snake -> PascalCase)
// Default --dry-run. --apply untuk tulis.

import { readdirSync, statSync, mkdirSync, renameSync } from 'node:fs';
import { join, extname } from 'node:path';
import { fileURLToPath } from 'node:url';

export function toPascalCase(name) {
  const base = name.replace(/\.(php)$/i, '');
  return base.split(/[_\-]/).map(p => p.charAt(0).toUpperCase() + p.slice(1)).join('') + '.php';
}

export function classifyTarget(relPath) {
  const norm = relPath.replace(/\\/g, '/');
  if (norm.startsWith('models/'))      return { dest: 'app/Models',      rename: true  };
  if (norm.startsWith('controllers/')) return { dest: 'app/Controllers', rename: false };
  if (norm.startsWith('libraries/'))   return { dest: 'app/Libraries',   rename: false };
  if (norm.startsWith('helpers/'))     return { dest: 'app/Helpers',     rename: false };
  if (norm.startsWith('config/'))      return { dest: 'app/Config',      rename: false };
  return null; // views, hooks, third_party, dll — tidak dihandle
}

export function planRename(applicationDir, appDir) {
  const plan = [];
  function walk(dir) {
    for (const e of readdirSync(dir)) {
      const full = join(dir, e);
      const s = statSync(full);
      if (s.isDirectory()) { walk(full); continue; }
      if (extname(e) !== '.php') continue;
      const rel = full.slice(applicationDir.length + 1).replace(/\\/g, '/');
      const cls = classifyTarget(rel);
      if (!cls) continue;
      const newName = cls.rename ? toPascalCase(e) : e;
      plan.push({ from: full, to: join(appDir, cls.dest, newName), rel, dest: cls.dest, newName });
    }
  }
  walk(applicationDir);
  return plan;
}

function main() {
  const args = process.argv.slice(2);
  const apply = args.includes('--apply');
  const ci3App = args.find(a => !a.startsWith('--') && a.includes('application'));
  const ci4App = args.find(a => !a.startsWith('--') && a.includes('app'));
  if (!ci3App || !ci4App) {
    console.error('Usage: node rename-files.mjs <ci3-application-dir> <ci4-app-dir> [--apply]');
    process.exit(1);
  }
  const plan = planRename(ci3App, ci4App);
  if (plan.length === 0) { console.log('Tidak ada file untuk direname.'); return; }
  console.log(`${plan.length} file direncanakan [${apply ? 'APPLIED' : 'DRY-RUN'}]:`);
  for (const p of plan) {
    console.log(`  ${p.rel}  ->  ${p.dest}/${p.newName}`);
    if (apply) {
      mkdirSync(join(ci4App, p.dest), { recursive: true });
      renameSync(p.from, p.to);
    }
  }
  if (!apply) console.log('\n(dry-run; --apply untuk eksekusi)');
}

if (process.argv[1] === fileURLToPath(import.meta.url)) main();
