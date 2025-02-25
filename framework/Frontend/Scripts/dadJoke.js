// framework/Frontend/Scripts/dadJoke.js
import fetch from 'node-fetch';

// Function to fetch a dad joke
export async function fetchDadJoke() {
  try {
    const response = await fetch('https://icanhazdadjoke.com/', {
      headers: { 'Accept': 'application/json' }
    });
    const data = await response.json();
    return data.joke;
  } catch (error) {
    return 'Why don’t skeletons fight each other? They don’t have the guts.';
  }
}