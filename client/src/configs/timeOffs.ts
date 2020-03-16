/*
 * This is a local configuration file.
 *
 * You should adjust the values of these settings and rename the file to
 * 'timeOffs.local.ts'.
 *
 */

// Remember to update the db table ddl as well, when updating here!
const timeOffsConfig = [
  { text: "Urlaub", value: "urlaub" },
  { text: "Gleitzeit-Tag", value: "gleitzeit" },
  { text: "AZV-Tag", value: "azv" }
];

export default timeOffsConfig;
