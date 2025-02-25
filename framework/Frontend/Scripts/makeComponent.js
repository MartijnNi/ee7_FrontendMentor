import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import readline from 'readline';
import { exec } from 'child_process';
import { promisify } from 'util';
import { logWithHeading, displayCelebration, printErrorWithJoke, displayFatal } from './logger.js';
import { fetchDadJoke } from './dadJoke.js';

// Convert exec to return a promise
const execPromise = promisify(exec);

// Determine the directory of the current script
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Define a function to locate the project root
function findProjectRoot(startDir) {
  let currentDir = startDir;

  while (currentDir !== path.parse(currentDir).root) {
    if (fs.existsSync(path.join(currentDir, 'package.json'))) {
      return currentDir;
    }
    currentDir = path.dirname(currentDir);
  }

  throw new Error('Project root not found');
}

// Define the directories
const projectRoot = findProjectRoot(__dirname);
const componentsDir = path.resolve(projectRoot, 'resources/components');

// Get the component name from the command line arguments
const componentName = process.argv[2];

if (!componentName) {
  displayFatal('\x1b[31mPlease provide a component name.\x1b[0m'); // Red text
  process.exit(1);
}

// Define paths for the new component
const componentDir = path.resolve(componentsDir, componentName);
const scssFilePath = path.resolve(componentDir, `${componentName}.scss`);
const jsFilePath = path.resolve(componentDir, `${componentName}.js`);
const pugFilePath = path.resolve(componentDir, `${componentName}.pug`);

// Check if the component directory already exists
if (fs.existsSync(componentDir)) {
  displayFatal(`\x1b[31mComponent directory already exists:\n${componentDir}\x1b[0m`); // Red text
  
  // Fetch dad joke
  const joke = await fetchDadJoke();

  // Array of pun options for the error message
  const errorPuns = [
    "Looks like this component directory is already on the map!\nTime for a Dad Joke to navigate away from this error:",
    "Well, well, well, this directory already exists!\nLet's crack a Dad Joke to ease the path forward:",
    "Seems like this component directory is already booked!\nHere’s a Dad Joke to lighten the load:",
    "The directory’s already taken!\nHere’s a Dad Joke to make sure your mood isn't occupied:",
    "Oops! This component directory already exists.\nLet’s bring in a Dad Joke to break the ice:",
    "The component directory’s already in place!\nHere’s a Dad Joke to make sure you’re not in a bind:",
    "Looks like this directory's got a head start!\nLet's distract ourselves with a Dad Joke while we regroup:",
    "It appears the component directory’s already set up shop!\nHere’s a Dad Joke to lift your spirits:",
    "This directory is already hosting a component!\nTime for a Dad Joke to ease the congestion:",
    "The directory is already occupied!\nHere’s a Dad Joke to help you lighten up:"
  ];
  
  // Define the error message
  const errorMessage = errorPuns[Math.floor(Math.random() * errorPuns.length)] + '\n';

  // Print the error message and joke
  printErrorWithJoke(errorMessage, joke);
  process.exit(1);
}

logWithHeading(`Creating component: ${componentName}`);

// Create the new component directory and subdirectories
fs.mkdirSync(componentDir, { recursive: true });

// Create SCSS file with basic content
const scssContent = `// Styles for ${componentName}\n\n.${componentName.toLowerCase()} {\n  // Add styles for ${componentName} here\n}\n`;
fs.writeFileSync(scssFilePath, scssContent, 'utf8');
console.log(`\n\x1b[32mSCSS file created: ${path.relative(projectRoot, scssFilePath)}\x1b[0m`); // Cyan text

// Create Pug file with boilerplate content
const pugContent = `mixin ${componentName}()\n  .${componentName.toLowerCase()}\n    // Add content for ${componentName} here\n`;
fs.writeFileSync(pugFilePath, pugContent, 'utf8');
console.log(`\x1b[32mPug file created: ${path.relative(projectRoot, pugFilePath)}\x1b[0m`); // Cyan text

// Prompt user to create a JS file
const rl = readline.createInterface({
  input: process.stdin,
  output: process.stdout
});

rl.question('Do you want to create a JavaScript file for this component? (y/n): ', async (answer) => {
  if (answer.toLowerCase() === 'y') {
    // Create JS file with basic content
    const jsContent = `// JavaScript for ${componentName}\n\n`;
    fs.writeFileSync(jsFilePath, jsContent, 'utf8');
    console.log(`\x1b[32mJS file created: ${path.relative(projectRoot, jsFilePath)}\x1b[0m\n`); // Green text
  } else {
    console.log('\x1b[33mNo JS file created.\x1b[0m\n'); // Yellow text
  }
  
  // Run the autoload script
  try {
    logWithHeading('Running Autoload Script');

    const { stdout, stderr } = await execPromise('npm run autoload', { cwd: projectRoot });

    if (stdout) {
      console.log(`\n\x1b[32mImports generated for js and scss\x1b[0m`); // Green text
    }
    if (stderr) {
      displayFatal(`\x1b[31mAutoload Error:\x1b[0m\n${stderr}`);
    }
  } catch (error) {
    displayFatal('\x1b[31mError running autoload script:\x1b[0m', error.message); // Red text
    const joke = await fetchDadJoke();
    console.error(`\x1b[33mDad Joke:\x1b[0m ${joke}`); // Yellow text
    process.exit(1);
  }

  rl.close();
  
  // Display celebration message
  displayCelebration('Component created successfully!');
});
