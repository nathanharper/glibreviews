Glib Movie Review Generator

A web app that automatically generates "glib" movie reviews. This started as an inside joke between me and my girlfriend, as we liked coming up with dismissive, punny review-fragments for popular films (ex: "Beasts of the Southern *Mild*"). It occurred to me that it doesn't take a lot of creative effort to come up with these gems, and that it would be fun to try to automate it.

The app isn't complete yet, but the plan is to:

1. Routinely query the Rotten Tomatoes API for newly released films, and store the data
2. Analyze the titles of the films and look for punnable words. This is broken down into two steps:

    * take all the words of the title and query the "RhymeBrain" API for rhyming words. Use this data to construct several alternate titles
    * if there's only one word in the title or we just want to pun deeper, use the Tex "hyphenator" to break words into syllables and look for puns. not sure yet if I'm going to use the Python wrapper or Perl wrapper for this library.

3. Save the punnable words to our database with a "score". Allow training of the app in the sense that if you go into training mode, it presents several different punny titles for you to choose from. Choosing one title will obviously increase that title's rank *and* the rank of the words included in it, so that the app will favor titles including these words in the future.
