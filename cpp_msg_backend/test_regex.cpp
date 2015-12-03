#include <cstdio>
#include <string>
#include <cstring>
#include <boost/regex.hpp>

#define MAX_LEN 1024
int main() {

	char str[ MAX_LEN ];
	std::string input;
	boost::regex *verify_ = new boost::regex("^<notify><email>(.+)</email><addr>(.+)</addr><note>(.+)</note></notify>$");

	while( 1 ) {

		printf("input: ");
		fgets(str, MAX_LEN, stdin);

		if ((strlen(str)>0) && (str[strlen (str) - 1] == '\n'))
		str[strlen (str) - 1] = '\0';

		input = str;
		printf("length of the string: %lu\n", input.length());
		if (regex_match(input, *verify_)) printf("It's a Name\n");
	}

	delete verify_;
	return 0;
}

/* Testcases:
<name>a</name>
<name>Juan Poma</name>
<name>$#%#!@$%@#$%@$#%@$5  </name>
<latitude>23</latitude>
<latitude>23.00</latitude>
<latitude>-49.67</latitude>
<latitude>-49.</latitude>
<coord><name>Juan Poma</name><latitude>-73.68</latitude><longitude>42.72</longitude></coord>
<coord><name>Juan Poma</name><latitude>-73.68</latitude><longitude>42.72</longitude></coord>
<coord><name>Juan Poma</name><latitude>-73.68</latitude><longitude>42.72</logitude></coord>
<coord><name>Juan Poma</name><latitude>-73.68</latitude><longitude>42.72</logitude></coord>
<notify><email>pj@rpi.edu</email><addr>bouton rd,</addr><note>need wheel chair</note></notify>


*/
