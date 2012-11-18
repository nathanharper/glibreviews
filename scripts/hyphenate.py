import sys
import json

""" 2.7 and up version is capitalized (annoying) """
if sys.version_info >= (2, 7):
    from hyphen import Hyphenator, dictools
    hy = Hyphenator('en_US')
else:
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
