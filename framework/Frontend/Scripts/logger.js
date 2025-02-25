// Define maximum length for the log output
const MAX_LOG_LENGTH = 70;

// Function to create a separator line based on length
function createSeparatorLine(length) {
  return '-'.repeat(length);
}

// Function to wrap text by whole words
function wrapText(text, maxLength) {
  const words = text.split(' ');
  let currentLine = '';
  const lines = [];

  words.forEach(word => {
    if ((currentLine + word).length > maxLength) {
      lines.push(currentLine);
      currentLine = word + ' ';
    } else {
      currentLine += word + ' ';
    }
  });

  if (currentLine) {
    lines.push(currentLine.trim());
  }

  return lines;
}

// Function to log with an info heading
export function logWithHeading(heading, colorCode = '\x1b[36m') {
  console.log(`${colorCode}${createSeparatorLine(MAX_LOG_LENGTH)}\x1b[0m`);
  console.log(`${colorCode}${heading}\x1b[0m`);
  console.log(`${colorCode}${createSeparatorLine(MAX_LOG_LENGTH)}\x1b[0m`);
}

// Function to display a celebratory message with emojis
export function displayCelebration(message) {
  console.log(`\n\x1b[32m🎉🎉🎉 ${message} 🎉🎉🎉\x1b[0m\n`); // Green text with emojis
}

// Function to display a fatal error message with emojis
export function displayFatal(message) {
  const emoji = '💀';  
  console.error(`\x1b[31m${emoji} ${message} \x1b[0m\n`); // Red text with death emojis
}


// Function to print an error message with a dad joke
export function printErrorWithJoke(errorMessage, joke) {
  const separatorLength = MAX_LOG_LENGTH; // Ensure minimum length for visual balance
  const separatorLine = createSeparatorLine(separatorLength);

  console.error(`${separatorLine}`);
  console.error(`\x1b[33m${errorMessage}\x1b[0m`);
  wrapText(joke, MAX_LOG_LENGTH).forEach(line => console.error(`${line}`));
  console.error(`${separatorLine}`);
}
