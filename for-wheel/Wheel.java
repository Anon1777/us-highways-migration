import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileWriter;
import java.io.IOException;
import java.io.PrintWriter;
import java.util.ArrayList;
import java.util.Random;
import java.util.Scanner;

// javac .\\us-highways-migration\for-wheel\Wheel.java
// java -cp us-highways-migration for-wheel.Wheel

public class Wheel {
    public static void main(String[] args){
        ArrayList<String> roadName = new ArrayList<>();
        ArrayList<Double> weights = new ArrayList<>();
        double totalWeight = 0;
        try(Scanner in = new Scanner(new File("./us-highways-migration/for-wheel/weighted-roads.txt"))){
            while(in.hasNextLine()){
                String[] parts = in.nextLine().split(",");
                double weight = Double.parseDouble(parts[0]);
                weights.add(weight);
                roadName.add(parts[1]);
                totalWeight += weight;
            }
        } catch (FileNotFoundException e) {
            System.err.println("Program exited with code 1 - Could not read file.");
        }
        Random rand = new Random();
        double random = rand.nextDouble() * totalWeight;
        double cumulative = 0;
        int selectedIndex = 0;
        for(int i = 0; i < weights.size(); i++){
            cumulative += weights.get(i);
            if(random < cumulative){
                selectedIndex = i;
                break;
            }
        }
        // int r = rand.nextInt(roadName.size());
        try(FileWriter fw = new FileWriter(new File("./us-highways-migration/for-wheel/wheel-results.txt"), true);
            PrintWriter out = new PrintWriter(fw)
        ){
            out.println(roadName.get(selectedIndex));
            System.out.println("Program exited with code 0 - Successful spin.");
        } catch (IOException e) {
            System.err.println("Program exited with code 2 - Could not write to file.");
        }
    }
}