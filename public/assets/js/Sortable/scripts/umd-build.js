import build from './build.js';

export default ([
  {
    input: 'entry/entry-complete.js',
    output: {
      ...build.output,
      file: './Sortable.js',
      format: 'umd',
    },
  },
]).map((config) => {
  const buildCopy = { ...build };
  return Object.assign(buildCopy, config);
});
