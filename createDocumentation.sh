#!/bin/bash

# first run (creates config file)
# java -jar ../IKON/tools/WIDOCO/jar/widoco-1.4.1-jar-with-dependencies.jar -ontFile ./dfg-fachsystematik.owl -outFolder ./docs -getOntologyMetadata -oops -rewriteAll -saveConfig ./widocoConfigFile -includeImportedOntologies -htaccess -webVowl -licensius

# uses config file
java -jar ../IKON/tools/WIDOCO/jar/widoco-1.4.1-jar-with-dependencies.jar -ontFile ./dfg-fachsystematik.owl -outFolder ./docs -getOntologyMetadata -oops -rewriteAll -confFile ./widocoConfigFile -includeImportedOntologies -htaccess -webVowl -licensius
