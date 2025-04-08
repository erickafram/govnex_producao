import { Transaction } from "@/types";
import { v4 as uuidv4 } from "uuid";

// Mock QR code generation using a plain text representation
const generateQRCode = (pixCode: string): string => {
  // In a real application, this would generate a proper QR code image URL
  return `data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==`;
};

export const createPixPayment = async (userId: string, amount: number): Promise<Transaction> => {
  // Validate amount
  if (amount < 400) {
    throw new Error("O valor mínimo para recarga é de R$ 400,00");
  }

  // Generate a unique transaction ID
  const transactionId = uuidv4();
  
  // Create a PIX code (a real implementation would call a payment API)
  const pixCode = `PIX${transactionId.substring(0, 8).toUpperCase()}`;
  
  // Generate QR code URL
  const qrCodeUrl = generateQRCode(pixCode);
  
  // Create transaction record
  const transaction: Transaction = {
    id: transactionId,
    userId,
    type: "deposit",
    amount,
    status: "pending",
    description: `Recarga via PIX - R$ ${amount.toFixed(2)}`,
    createdAt: new Date().toISOString(),
    pixCode,
    qrCodeUrl
  };
  
  // Simulate saving to database
  console.log("Created PIX payment:", transaction);
  
  return transaction;
};

export const checkPaymentStatus = async (transactionId: string): Promise<string> => {
  // Simulate checking payment status with a payment provider
  // In a real app, this would make an API call
  
  // Randomly return a status for demonstration purposes
  const statuses = ["pending", "completed", "failed"];
  const randomIndex = Math.floor(Math.random() * statuses.length);
  return statuses[randomIndex];
};

export const simulatePaymentCompletion = async (transaction: Transaction): Promise<Transaction> => {
  // This function simulates a payment being completed
  // In a real app, this would be triggered by a webhook from the payment provider
  
  return {
    ...transaction,
    status: "completed",
  };
};
