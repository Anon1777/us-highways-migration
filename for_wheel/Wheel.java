package for_wheel;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileWriter;
import java.io.IOException;
import java.io.PrintWriter;
import java.util.ArrayList;
import java.util.Random;
import java.util.Scanner;

public class Wheel {
    public static void main(String[] args){
        ArrayList<String> roadName = new ArrayList<>();
        try(Scanner in = new Scanner(new File("./for_wheel/weighted_roads.txt"))){
            while(in.hasNextLine()){
                String[] parts = in.nextLine().split(",");
                roadName.add(parts[1]);
            }
        } catch (FileNotFoundException e) {
            System.err.println("Program exited with code 1 - Could not read file.");
        }
        Random rand = new Random();
        int r = rand.nextInt(roadName.size()-1);
        try(FileWriter fw = new FileWriter(new File("./for_wheel/wheel_results.txt"), true);
            PrintWriter out = new PrintWriter(fw)
        ){
            out.println(roadName.get(r));
            System.out.println("Program exited with code 0 - Successful spin.");
        } catch (IOException e) {
            System.err.println("Program exited with code 2 - Could not write to file.");
        }
    }
}