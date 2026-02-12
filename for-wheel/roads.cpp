#include <iostream>
#include <fstream>
#include <string>
#include <map>
using namespace std;

// c++ .\\us-highways-migration\\for-wheel\\roads.cpp -o .\\us-highways-migration\\for-wheel\\road-weight.exe
// .\\us-highways-migration\\for-wheel\\road-weight.exe

int main() {
    ifstream infile(".\\us-highways-migration\\for-wheel\\roads.txt"); // Input file with all your roads, one per line
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
    ofstream outfile(".\\us-highways-migration\\for-wheel\\weighted-roads.txt");
    if (!outfile) {
        cerr << "Error creating output file!" << endl;
        return 1;
    }

    for (const auto& pair : roadCounts) {
        outfile << pair.second << "," << pair.first << endl;
    }

    outfile.close();
    cout << "Done! Weighted road list saved to weighted-roads.txt" << endl;
    return 0;
}