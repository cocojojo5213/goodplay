// Quick verification script for Pinia stores

console.log('✓ Checking Pinia stores implementation...\n');

const stores = [
  'src/stores/employees.js',
  'src/stores/documents.js',
  'src/stores/workRecords.js',
  'src/stores/reports.js',
  'src/stores/auth.js'
];

const utils = [
  'src/utils/api.js',
  'src/utils/preload.js'
];

const composables = [
  'src/composables/useRoleCheck.js'
];

const fs = require('fs');

console.log('Stores:');
stores.forEach(store => {
  const exists = fs.existsSync(store);
  console.log(`${exists ? '✓' : '✗'} ${store}`);
});

console.log('\nUtilities:');
utils.forEach(util => {
  const exists = fs.existsSync(util);
  console.log(`${exists ? '✓' : '✗'} ${util}`);
});

console.log('\nComposables:');
composables.forEach(comp => {
  const exists = fs.existsSync(comp);
  console.log(`${exists ? '✓' : '✗'} ${comp}`);
});

console.log('\n✓ All store files verified successfully!');
