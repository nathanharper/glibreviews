import sys
import json
from hyphen import hyphenator, dictools

hy = hyphenator('en_US')
try:
    json_object = {}
    for word in sys.argv[1:]:
        json_object[word] = hy.syllables(unicode(word))
    print json.dumps(json_object)
except IndexError:
    sys.exit(1)

sys.exit(0)
