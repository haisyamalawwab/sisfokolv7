import { test } from 'node:test';
import assert from 'node:assert/strict';
import { toPascalCase, classifyTarget } from '../rename-files.mjs';

test('user_model.php -> UserModel.php', () => {
  assert.equal(toPascalCase('user_model.php'), 'UserModel.php');
});
test('auth.php -> Auth.php', () => {
  assert.equal(toPascalCase('auth.php'), 'Auth.php');
});
test('foo_bar_baz.php -> FooBarBaz.php', () => {
  assert.equal(toPascalCase('foo_bar_baz.php'), 'FooBarBaz.php');
});
test('hyphenated name split too', () => {
  assert.equal(toPascalCase('foo-bar.php'), 'FooBar.php');
});
test('models/ -> app/Models, rename true', () => {
  assert.deepEqual(classifyTarget('models/user_model.php'), { dest: 'app/Models', rename: true });
});
test('controllers/ -> app/Controllers, no rename', () => {
  assert.deepEqual(classifyTarget('controllers/Auth.php'), { dest: 'app/Controllers', rename: false });
});
test('libraries/ -> app/Libraries, no rename', () => {
  assert.deepEqual(classifyTarget('libraries/Auth_lib.php'), { dest: 'app/Libraries', rename: false });
});
test('helpers/ -> app/Helpers, no rename', () => {
  assert.deepEqual(classifyTarget('helpers/custom_helper.php'), { dest: 'app/Helpers', rename: false });
});
test('config/ -> app/Config, no rename', () => {
  assert.deepEqual(classifyTarget('config/config.php'), { dest: 'app/Config', rename: false });
});
test('views/ not handled (returns null)', () => {
  assert.equal(classifyTarget('views/foo.php'), null);
});
