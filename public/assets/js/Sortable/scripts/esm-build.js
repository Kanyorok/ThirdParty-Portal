import build from './build.js';

export default ([
  {
    input: 'entry/entry-core.js',
    output: {
      ...build.output,
      file: 'modular/sortable.core.esm.js',
      format: 'esm',
    },
  },
  {
    input: 'entry/entry-defaults.js',
    output: {
      ...build.output,
      file: 'modular/sortable.esm.js',
      format: 'esm',
    },
  },
  {
    input: 'entry/entry-complete.js',
    output: {
      ...build.output,
      file: 'modular/sortable.complete.esm.js',
      format: 'esm',
    },
  },
]).map((config) => {
  const buildCopy = { ...build };
  return Object.assign(buildCopy, config);
});
