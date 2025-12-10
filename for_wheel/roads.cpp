#include <iostream>
#include <fstream>
#include <string>
#include <map>
using namespace std;

int main() {
    ifstream infile("C:\\Users\\joete\\Desktop\\coding\\significant projects\\seychelles_hub\\us-highways-migration\\for_wheel\\roads.txt"); // Input file with all your roads, one per line
    if (!infile) {
        cerr << "Error opening file!" << endl;
        return 1;
    }

    map<string, int> roadCounts;
    string line;

    while (getline(infile, line)) {
        // Skip lines containing &check; or &cross;
        if (line.find("&check;") != string::npos || line.find("&cross;") != string::npos) {
            continue;
        }

        // Trim whitespace (optional)
        size_t start = line.find_first_not_of(" \\t");
        size_t end = line.find_last_not_of(" \\t");
        if (start == string::npos) continue; // Skip empty lines
        string road = line.substr(start, end - start + 1);

        // Increment count
        roadCounts[road]++;
    }

    infile.close();

    // Output results
    ofstream outfile(".\\for_wheel\\weighted_roads.txt");
    if (!outfile) {
        cerr << "Error creating output file!" << endl;
        return 1;
    }

    for (const auto& pair : roadCounts) {
        outfile << pair.second << "," << pair.first << endl;
    }

    outfile.close();
    cout << "Done! Weighted road list saved to weighted_roads.txt" << endl;
    return 0;
}